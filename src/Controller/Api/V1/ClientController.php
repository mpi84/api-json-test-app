<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\ClientConvertCurrencyContext;
use App\DTO\ClientCreateContext;
use App\DTO\ClientUpdateContext;
use App\Entity\AppUser;
use App\Helpers\ApiRequestHelper;
use App\Helpers\ApiResponseHelper;
use App\Manager\ClientManager;
use App\Resolver\ApiRequestPayloadValueResolver;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Client')]
#[Security(name: 'Bearer')]
#[OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(
    type: 'object',
    example: '{"code": 401, "message": "Expired JWT Token"}'
))]
class ClientController extends AbstractController
{
    use ApiRequestHelper;
    use ApiResponseHelper;

    private ClientManager $clientManager;

    public function __construct(ClientManager $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    #[OA\Get(
        description: 'User with admin role can get any client, manager only can get own clients',
        summary: 'Get all Clients or one Client by id'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: false, allowEmptyValue: true, schema: new OA\Schema(type: 'int'))]
    #[OA\RequestBody(content: [new OA\MediaType(mediaType: 'application/json')])]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'array|object|null', example: '[{"id": 6, "email": "client1@test.local", "managerId": 5}, {"id": 7, "email": "client2@test.local", "managerId": 5}]'),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/client/{id}',
        name: 'api_client_info',
        requirements: ['id' => '.|\d+'],
        methods: ['GET'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function read(
        #[CurrentUser] AppUser $user,
        ?int $id = null
    ): JsonResponse {
        try {
            $result = null;
            $error = null;

            if ($id === null) {
                $result = $user->isAdmin() ?
                    $this->clientManager->getAllClients()
                    :
                    $this->clientManager->getAllClientsByManagerId($user->getId());
            } else {
                $result = $this->clientManager->getClientById($id, $user->isAdmin() ? null : $user->getId());
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result, $error);
    }

    #[OA\Post(
        description: 'User with admin role can create client for any manager, manager only for yourself',
        summary: 'Create Client'
    )]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'array|object|null', example: '{"id": 10, "email": "client10@test.local", "managerId": 5}'),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/client/create',
        name: 'api_client_create',
        methods: ['POST'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function create(
        #[CurrentUser] AppUser $user,
        #[MapRequestPayload(
            acceptFormat: 'json',
            resolver: ApiRequestPayloadValueResolver::class
        )] ClientCreateContext $clientContext,
        Request $request,
    ): JsonResponse {
        $client = null;

        try {
            $error = $this->getValidationErrors($request->attributes->get('_dto_validation_errors'));

            if (!$error) {
                if ($user->isAdmin()) {
                    $clientContext->managerId = $clientContext->managerId ?: $user->getId();
                } else {
                    $clientContext->managerId = $user->getId();
                }

                $client = $this->clientManager->createClient($clientContext);
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($client, $error);
    }

    #[OA\Post(
        description: 'User with admin role can update any client, manager only can update own clients',
        summary: 'Update Client by id'
    )]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'array|object|null', example: '{"id": 6, "email": "client15@test.local", "managerId": 5}'),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/client/update/{id}',
        name: 'api_client_update',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function update(
        #[CurrentUser] AppUser $user,
        #[MapRequestPayload(
            acceptFormat: 'json',
            resolver: ApiRequestPayloadValueResolver::class
        )] ClientUpdateContext $clientContext,
        Request $request,
        int $id
    ): JsonResponse {
        try {
            $error = $this->getValidationErrors(
                $request->attributes->get('_dto_validation_errors'),
                $clientContext
            );

            if (!$error) {
                if (!$user->isAdmin()) {
                    $clientContext->managerId = null;
                }

                $result = $this->clientManager->updateClient(
                    $id,
                    $clientContext,
                    $user->isAdmin() ? null : $user->getId()
                );
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result ?? null, $error);
    }

    #[OA\Delete(
        description: "User with admin role can delete any client, manager only can delete own clients
            \nAny accounts associated with client will be deleted also",
        summary: 'Delete Client by id'
    )]
    #[OA\RequestBody(content: [new OA\MediaType(mediaType: 'application/json')])]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'bool|null', example: true),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/client/{id}',
        name: 'api_client_delete',
        requirements: ['id' => '\d+'],
        methods: ['DELETE'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function delete(
        #[CurrentUser] AppUser $user,
        int $id
    ): JsonResponse {
        try {
            $result = $this->clientManager->deleteClientById($id, $user->isAdmin() ? null : $user->getId());
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result ?? null, $error ?? null);
    }

    #[OA\Post(
        description: "Convert currency between two client's existing accounts
        \n RUB: ->USD 0.01 ->EUR 0.009 
        \n USD: ->RUB 90 ->EUR 0.8 
        \n EUR: ->RUB 110 ->USD 1.2",
        summary: 'Convert currency between two accounts'
    )]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'array|null', example: '[{"id": 12,"client": 3,"currency": "eur","amount": 0},{"id": 4,"client": 3,"currency": "usd","amount": 221}]'),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/client/convert_currency',
        name: 'api_client_convert_currency',
        methods: ['POST'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function convert(
        #[CurrentUser] AppUser $user,
        #[MapRequestPayload(
            acceptFormat: 'json',
            resolver: ApiRequestPayloadValueResolver::class
        )] ClientConvertCurrencyContext $context,
        Request $request,
    ): JsonResponse {
        try {
            $error = $this->getValidationErrors($request->attributes->get('_dto_validation_errors'));

            if (!$error) {
                $result = $this->clientManager->convertClientCurrency($context, $user->isAdmin() ? null : $user->getId());
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result ?? null, $error ?? null);
    }
}

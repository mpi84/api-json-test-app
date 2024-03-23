<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\AccountCreateContext;
use App\DTO\AccountUpdateContext;
use App\Entity\AppUser;
use App\Helpers\ApiRequestHelper;
use App\Helpers\ApiResponseHelper;
use App\Manager\AccountManager;
use App\Resolver\ApiRequestPayloadValueResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Account')]
#[Security(name: 'Bearer')]
#[OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(
    type: 'object',
    example: '{"code": 401, "message": "Expired JWT Token"}'
))]
class AccountController extends AbstractController
{
    use ApiRequestHelper;
    use ApiResponseHelper;

    private AccountManager $accountManager;

    public function __construct(AccountManager $accountManager)
    {
        $this->accountManager = $accountManager;
    }

    #[OA\Get(
        description: 'User with admin role can get any client account, manager only can get own client\'s account',
        summary: 'Get all Accounts or one Account by id'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: false, allowEmptyValue: true, schema: new OA\Schema(type: 'int'))]
    #[OA\RequestBody(content: [new OA\MediaType(mediaType: 'application/json')])]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'array|object|null', example: '{"id": 20, "client": 9, "currency": "usd", "amount": 650}'),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/account/{id}',
        name: 'api_account_info',
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
                    $this->accountManager->getAllAccounts()
                    :
                    $this->accountManager->getAllAccountsByManagerId($user->getId());
            } else {
                $result = $this->accountManager->getAccountById($id, $user->isAdmin() ? null : $user->getId());
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result, $error);
    }

    #[OA\Post(
        description: 'User with admin role can create account for any client, manager only for own client',
        summary: 'Create Account'
    )]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'array|object|null', example: '{"id": 5, "client": 10, "currency": "rub", "amount": 5000}'),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/account/create',
        name: 'api_account_create',
        methods: ['POST'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function create(
        #[CurrentUser] AppUser $user,
        #[MapRequestPayload(
            acceptFormat: 'json',
            resolver: ApiRequestPayloadValueResolver::class
        )] AccountCreateContext $accountContext,
        Request $request,
    ): JsonResponse {
        $client = null;

        try {
            $error = $this->getValidationErrors($request->attributes->get('_dto_validation_errors'));

            if (!$error) {
                $client = $this->accountManager->createAccount($accountContext, $user->isAdmin() ? null : $user->getId());
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($client, $error);
    }

    #[OA\Post(
        description: 'User with admin role can update any account, manager only can update own client\'s account',
        summary: 'Update Account by id'
    )]
    #[Route(
        path: '/api/v1/account/update/{id}',
        name: 'api_account_update',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function update(
        #[CurrentUser] AppUser $user,
        #[MapRequestPayload(
            acceptFormat: 'json',
            resolver: ApiRequestPayloadValueResolver::class
        )] AccountUpdateContext $accountContext,
        Request $request,
        int $id
    ): JsonResponse {
        try {
            $error = $this->getValidationErrors(
                $request->attributes->get('_dto_validation_errors'),
                $accountContext
            );

            if (!$error) {
                $result = $this->accountManager->updateAccount(
                    $id,
                    $accountContext,
                    $user->isAdmin() ? null : $user->getId()
                );
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result ?? null, $error);
    }

    #[OA\Delete(
        description: "User with admin role can delete any account, manager only can delete own client's account",
        summary: 'Delete Account by id'
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
        path: '/api/v1/account/{id}',
        name: 'api_account_delete',
        methods: ['DELETE'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function delete(
        #[CurrentUser] AppUser $user,
        int $id
    ): JsonResponse {
        try {
            $result = $this->accountManager->deleteAccountById($id, $user->isAdmin() ? null : $user->getId());
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result ?? null, $error ?? null);
    }
}

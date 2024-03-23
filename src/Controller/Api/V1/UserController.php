<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\UserCreateContext;
use App\DTO\UserUpdateContext;
use App\Entity\AppUser;
use App\Helpers\ApiRequestHelper;
use App\Helpers\ApiResponseHelper;
use App\Manager\UserManager;
use App\Resolver\ApiRequestPayloadValueResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'User')]
#[Security(name: 'Bearer')]
#[OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(
    type: 'object',
    example: '{"code": 401, "message": "Expired JWT Token"}'
))]
class UserController extends AbstractController
{
    use ApiRequestHelper;
    use ApiResponseHelper;

    private UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    #[OA\Get(
        description: 'User with admin role see all Users, manager only Users with ROLE_MANAGER',
        summary: 'Get all Users or one User by id'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: false, allowEmptyValue: true, schema: new OA\Schema(type: 'int'))]
    #[OA\RequestBody(content: [new OA\MediaType(mediaType: 'application/json')])]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'array|object|null', example: '{"id": 4,"email": "admin@test.local", "roles" :["ROLE_ADMIN"]}'),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/user/{id}',
        name: 'api_user_info',
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
                $result = $this->userManager->getAllUsers($user->isAdmin());
            } else {
                $result = $this->userManager->getUserById($id, $user->isAdmin());
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result, $error);
    }

    #[OA\Post(summary: 'Create User')]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'array|object|null', example: '{"id": 10,"email": "admin10@test.local", "roles" :["ROLE_ADMIN"]}'),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/user/create',
        name: 'api_user_create',
        methods: ['POST'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function create(
        #[CurrentUser] AppUser $user,
        #[MapRequestPayload(
            acceptFormat: 'json',
            resolver: ApiRequestPayloadValueResolver::class
        )] UserCreateContext $userContext,
        Request $request,
    ): JsonResponse {
        $newUser = null;

        try {
            $error = $this->getValidationErrors($request->attributes->get('_dto_validation_errors'));

            if (!$error && $user->isAdmin()) {
                $newUser = $this->userManager->createUser($userContext);
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($newUser, $error);
    }

    #[OA\Post(description: 'Only User with ROLE_ADMIN can update Users', summary: 'Update User by id')]
    #[OA\Response(response: 200, description: 'Ok', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'result', type: 'array|object|null', example: '{"id": 10,"email": "admin10@test.local", "roles" :["ROLE_ADMIN"]}'),
            new OA\Property(property: 'error', type: 'array|object|null', example: null),
        ],
        type: 'object',
    ))]
    #[Route(
        path: '/api/v1/user/update/{id}',
        name: 'api_user_update',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function update(
        #[CurrentUser] AppUser $user,
        #[MapRequestPayload(
            acceptFormat: 'json',
            resolver: ApiRequestPayloadValueResolver::class
        )] UserUpdateContext $userContext,
        Request $request,
        int $id
    ): JsonResponse {
        try {
            $error = $this->getValidationErrors($request->attributes->get('_dto_validation_errors'), $userContext);

            if (!$error && $user->isAdmin()) {
                $result = $this->userManager->updateUser($id, $userContext);
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result ?? null, $error);
    }

    #[OA\Delete(
        description: "Only User with ROLE_ADMIN can delete User
            \n Can delete any User but not yourself and without related clients",
        summary: 'Delete User by id'
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
        path: '/api/v1/user/{id}',
        name: 'api_user_delete',
        requirements: ['id' => '\d+'],
        methods: ['DELETE'],
        condition: "request.headers.get('Content-Type') matches '/^application\\\/json$/i'"
    )]
    public function delete(#[CurrentUser] AppUser $user, int $id): JsonResponse
    {
        try {
            if ($user->isAdmin() && $user->getId() !== $id) {
                $result = $this->userManager->deleteUserById($id);
            }
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->prepareResponse($result ?? null, $error ?? null);
    }
}

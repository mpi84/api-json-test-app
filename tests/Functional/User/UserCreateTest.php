<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/User
 */
class UserCreateTest extends ApiTestCase
{
    public function testUserCreateNoToken(): void
    {
        self::$client->jsonRequest('POST', '/api/v1/user/create');
        self::assertResponseStatusCodeSame(401);
    }

    public function testUserCreateByAdminWithEmptyParams(): void
    {
        // Create with empty input
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/user/create', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(3, $response['error']);
    }

    public function testUserCreateByAdminWith2InvalidParams(): void
    {
        // Create with invalid 2 of 3 params
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/user/create', [
            'email' => 'admin123@test.local',
            'password' => '',
            'role' => 'ROLE_123123',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(2, $response['error']);
    }

    public function testUserCreateByAdminWithValidParams(): void
    {
        // Create with valid params
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/user/create', [
            'email' => 'test@test.local',
            'password' => '123123123',
            'role' => 'ROLE_MANAGER',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(5, $response['result']);
        self::assertArrayHasKey('createdAt', $response['result']);
        self::assertArrayHasKey('updatedAt', $response['result']);
        self::assertEquals('test@test.local', $response['result']['email']);
    }

    public function testUserCreateByManagerWithValidParams(): void
    {
        // Create with valid params
        $token = $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/user/create', [
            'email' => 'test@test.local',
            'password' => '123123123',
            'role' => 'ROLE_MANAGER',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }
}

<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/User
 */
class UserInfoTest extends ApiTestCase
{
    public function testUserInfoNoToken(): void
    {
        self::$client->jsonRequest('GET', '/api/v1/user');
        self::assertResponseStatusCodeSame(401);
    }

    public function testUserInfoByAdminAllUsers(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Get all Users
        $this->apiJsonRequest('GET', '/api/v1/user', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(4, $response['result']);
        self::assertCount(5, $response['result'][0]);
    }

        public function testUserInfoByAdminOneUserById(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Get one User by id
        $this->apiJsonRequest('GET', '/api/v1/user/1', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(5, $response['result']);
        self::assertArrayHasKey('createdAt', $response['result']);
        self::assertArrayHasKey('updatedAt', $response['result']);
        self::assertEquals(1, $response['result']['id']);
    }

    public function testUserInfoByManagerAllUsers(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        // Get all Users with manager role by manager
        $this->apiJsonRequest('GET', '/api/v1/user', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(3, $response['result']);
    }

        public function testUserInfoByManagerOneUserManagerById(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        // Get one User with admin role by id by manager
        $this->apiJsonRequest('GET', '/api/v1/user/1', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }
}

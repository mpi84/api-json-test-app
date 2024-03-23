<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/User
 */
class UserUpdateTest extends ApiTestCase
{
    public function testUserUpdateNoToken(): void
    {
        self::$client->jsonRequest('POST', '/api/v1/user/update/1');
        self::assertResponseStatusCodeSame(401);
    }

    public function testUserUpdateByAdminWithoutId(): void
    {
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update without id
        $this->apiJsonRequest('POST', '/api/v1/user/update', [], $token);
        self::assertResponseStatusCodeSame(404);
    }

    public function testUserUpdateByAdminWithNotExistingIdWithoutParams(): void
    {
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update with not existing id without params
        $this->apiJsonRequest('POST', '/api/v1/user/update/999', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(1, $response['error']);
    }

    public function testUserUpdateByAdminWithNotExistingIdWithValidParams(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update with not existing id and with valid param
        $this->apiJsonRequest('POST', '/api/v1/user/update/999', [
            'role' => AppUser::USER_ADMIN_ROLE,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testUserUpdateByAdminWithExistingIdWithValidParams(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update with existing id and with valid param
        $this->apiJsonRequest('POST', '/api/v1/user/update/3', [
            'email' => 'test1@test.local',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(3, $response['result']);
        self::assertEquals('test1@test.local', $response['result']['email']);
        self::assertNull($response['error']);
    }

    public function testUserUpdateByManagerWithExistingIdWithValidParams(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        // Update with existing id and with valid param
        $this->apiJsonRequest('POST', '/api/v1/user/update/4', [
            'email' => 'test2@test.local',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }
}

<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/User
 */
class UserDeleteTest extends ApiTestCase
{
    public function testUserDeleteNoToken(): void
    {
        self::$client->jsonRequest('DELETE', '/api/v1/user/1');
        self::assertResponseStatusCodeSame(401);
    }

    public function testUserDeleteByAdminWithNotExistingId(): void
    {
        // Delete with no existing id
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/user/999', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testUserDeleteByAdminYourself(): void
    {
        // Delete yourself with existing id
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/user/1', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testUserDeleteByAdminWithExistingIdWithRelatedClients(): void
    {
        // Delete with existing id with related client
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/user/2', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsString($response['error']);
    }

    public function testUserDeleteByAdminWithExistingIdWithoutRelatedClients(): void
    {
        // Delete with existing id without related client
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/user/4', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertEquals(true, $response['result']);
        self::assertNull($response['error']);
    }

    public function testUserDeleteByManagerWithExistingIdWithoutRelatedClients(): void
    {
        // Delete with existing id without related client
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/user/5', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

}

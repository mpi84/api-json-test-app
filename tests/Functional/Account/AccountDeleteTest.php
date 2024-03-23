<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/Account
 */
class AccountDeleteTest extends ApiTestCase
{
    public function testAccountDeleteNoToken(): void
    {
        self::$client->jsonRequest('DELETE', '/api/v1/account/1');
        self::assertResponseStatusCodeSame(401);
    }

    public function testAccountDeleteByAdminWithNotExistingId(): void
    {
        // Delete with no existing id by admin
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/account/999', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testAccountDeleteByAdminWithExistingId(): void
    {
        // Delete with existing id by admin
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/account/5', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertEquals(true, $response['result']);
        self::assertNull($response['error']);
    }

    public function testAccountDeleteByManagerWithExistingIdWithoutRelatedClients(): void
    {
        // Delete with existing id without related client with current manager
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/account/6', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testAccountDeleteByManagerWithExistingIdWithRelatedClient(): void
    {
        // Delete with existing id with related client with current manager
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/account/2', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertEquals(true, $response['result']);
        self::assertNull($response['error']);
    }
}

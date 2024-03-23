<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/Client
 */
class ClientDeleteTest extends ApiTestCase
{
    public function testClientDeleteNoToken(): void
    {
        self::$client->jsonRequest('DELETE', '/api/v1/client/1');
        self::assertResponseStatusCodeSame(401);
    }

    public function testClientDeleteByAdminWithNotExistingId(): void
    {
        // Delete with no existing id by admin
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/client/999', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testClientDeleteByAdminWithExistingId(): void
    {
        // Delete with existing id by admin
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/client/5', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertEquals(true, $response['result']);
        self::assertNull($response['error']);
    }

    public function testClientDeleteByManagerWithExistingIdWithoutRelatedClients(): void
    {
        // Delete with existing id without related client with current manager
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/client/5', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testClientDeleteByManagerWithExistingIdWithRelatedClients(): void
    {
        // Delete with existing id with related client with current manager
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('DELETE', '/api/v1/client/2', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertEquals(true, $response['result']);
        self::assertNull($response['error']);
    }
}

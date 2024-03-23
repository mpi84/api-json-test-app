<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/Client
 */
class ClientInfoTest extends ApiTestCase
{
    public function testClientInfoNoToken(): void
    {
        self::$client->jsonRequest('GET', '/api/v1/client');
        self::assertResponseStatusCodeSame(401);
    }

    public function testClientInfoAllByAdmin(): void
    {
        // Get all Clients by admin
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('GET', '/api/v1/client', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(5, $response['result']);
    }

    public function testClientInfoOneByIdByAdmin(): void
    {
        // Get one Client by id by admin
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('GET', '/api/v1/client/3', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(3, $response['result']);
        self::assertEquals(3, $response['result']['id']);
    }

    public function testClientInfoAllByManager(): void
    {
        // Get all Clients related with current manager
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('GET', '/api/v1/client', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(3, $response['result']);
    }

    public function testClientInfoOneByIdByManager(): void
    {
        // Get one Client related with current manager
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('GET', '/api/v1/client/4', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }
}

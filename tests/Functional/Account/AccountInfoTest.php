<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/Account
 */
class AccountInfoTest extends ApiTestCase
{
    public function testAccountInfoNoToken(): void
    {
        self::$client->jsonRequest('GET', '/api/v1/account');
        self::assertResponseStatusCodeSame(401);
    }

    public function testAccountInfoAllByAdmin(): void
    {
        // Get all Accounts by admin
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('GET', '/api/v1/account', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(8, $response['result']);
    }

    public function testAccountInfoOneByIdByAdmin(): void
    {
        // Get one Account by id by admin
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('GET', '/api/v1/account/3', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(6, $response['result']);
        self::assertArrayHasKey('createdAt', $response['result']);
        self::assertArrayHasKey('updatedAt', $response['result']);
        self::assertEquals(3, $response['result']['id']);
    }

    public function testAccountInfoAllByManager(): void
    {
        // Get all Accounts related with current manager and his clients
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('GET', '/api/v1/account', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(5, $response['result']);
    }

    public function testAccountInfoOneByIdByManagerWithRelatedClient(): void
    {
        // Get one Account related with current manager and his client
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('GET', '/api/v1/account/3', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(6, $response['result']);
        self::assertArrayHasKey('createdAt', $response['result']);
        self::assertArrayHasKey('updatedAt', $response['result']);
    }

    public function testAccountInfoOneByIdByManagerWithNotRelatedClient(): void
    {
        // Get one Account not related with current manager
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('GET', '/api/v1/account/8', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }
}

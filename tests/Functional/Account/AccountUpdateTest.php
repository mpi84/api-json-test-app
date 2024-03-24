<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/Account
 */
class AccountUpdateTest extends ApiTestCase
{
    public function testAccountUpdateNoToken(): void
    {
        self::$client->jsonRequest('POST', '/api/v1/account/update/1');
        self::assertResponseStatusCodeSame(401);
    }

    public function testAccountUpdateByAdminWithoutId(): void
    {
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update without id
        $this->apiJsonRequest('POST', '/api/v1/account/update', [], $token);
        self::assertResponseStatusCodeSame(405);
    }

    public function testAccountUpdateByAdminWithNotExistingIdWithoutParams(): void
    {
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update with not existing id without params
        $this->apiJsonRequest('POST', '/api/v1/account/update/999', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(1, $response['error']);
    }

    public function testAccountUpdateByAdminWithNotExistingIdWithValidParams(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update with not existing id and with valid param
        $this->apiJsonRequest('POST', '/api/v1/account/update/999', [
            'currency' => 'usd',
            'amount' => 500,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testAccountUpdateByAdminWithExistingIdWithValidParams(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update with existing id and with valid param
        $this->apiJsonRequest('POST', '/api/v1/account/update/1', [
            'amount' => 100,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(6, $response['result']);
        self::assertArrayHasKey('createdAt', $response['result']);
        self::assertArrayHasKey('updatedAt', $response['result']);
        self::assertEquals(100, $response['result']['amount']);
        self::assertNull($response['error']);
    }

    public function testAccountUpdateByManagerWithNotRelatedClient(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        // Update with existing id and with valid param
        // Client and his account not related with current manager
        $this->apiJsonRequest('POST', '/api/v1/account/update/7', [
            'amount' => 100,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testAccountUpdateByManagerWithRelatedClient(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        // Update with existing id and with valid param
        // Client and his account related with current manager
        $this->apiJsonRequest('POST', '/api/v1/account/update/1', [
            'currency' => 'usd',
            'amount' => 100,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(6, $response['result']);
        self::assertArrayHasKey('createdAt', $response['result']);
        self::assertArrayHasKey('updatedAt', $response['result']);
        self::assertEquals(100, $response['result']['amount']);
        self::assertEquals('usd', $response['result']['currency']);
        self::assertNull($response['error']);
    }
}

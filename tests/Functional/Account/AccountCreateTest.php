<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/Account
 */
class AccountCreateTest extends ApiTestCase
{
    public function testAccountCreateNoToken(): void
    {
        self::$client->jsonRequest('POST', '/api/v1/account/create');
        self::assertResponseStatusCodeSame(401);
    }

    public function testAccountCreateByAdminWithEmptyParams(): void
    {
        // Create with empty input
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/account/create', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(2, $response['error']);
    }

    public function testAccountCreateByAdminWithInvalidParam(): void
    {
        // Create with 1 invalid params
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/account/create', [
            'currency' => 'usd',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(1, $response['error']);
    }

    public function testAccountCreateByAdminWithValidParams(): void
    {
        // Create with valid params by admin with custom manager id
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/account/create', [
            'currency' => 'eur',
            'clientId' => 5,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(4, $response['result']);
        self::assertEquals('eur', $response['result']['currency']);
        self::assertNull($response['error']);
    }

    public function testAccountCreateByManagerWithValidParams(): void
    {
        // Create with valid params by manager with related client
        $token = $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/account/create', [
            'currency' => 'usd',
            'clientId' => 1,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(4, $response['result']);
        self::assertEquals(1, $response['result']['client']);
        self::assertEquals('usd', $response['result']['currency']);
        self::assertNull($response['error']);
    }

    public function testAccountCreateByManagerWithSameCurrency(): void
    {
        // Create with valid params by manager with related client
        // But with already existing account with same currency
        $token = $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/account/create', [
            'currency' => 'rub',
            'clientId' => 1,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsString($response['error']);
    }

    public function testAccountCreateByManagerWithWrongClient(): void
    {
        // Create with valid params by manager with not related client
        $token = $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/account/create', [
            'currency' => 'rub',
            'clientId' => 5,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }
}

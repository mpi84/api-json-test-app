<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/Client
 */
class ClientConvertTest extends ApiTestCase
{
    public function testClientConvertNoToken(): void
    {
        self::$client->jsonRequest('POST', '/api/v1/client/convert_currency');
        self::assertResponseStatusCodeSame(401);
    }

    public function testClientConvertByAdminWithEmptyParams(): void
    {
        // Convert with empty input
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/convert_currency', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(3, $response['error']);
    }

    public function testClientConvertByAdminWithValidParamsWithSameCurrencies(): void
    {
        // Convert with valid input with same currencies
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/convert_currency', [
            'clientId' => 3,
            'currencyFrom' => 'rub', // 25550
            'currencyTo' => 'rub',   // 25550
            'amount' => 10000,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(2, $response['error']);
        self::assertEquals('Currencies cannot be same', $response['error']['currencyFrom']);
        self::assertEquals('Currencies cannot be same', $response['error']['currencyTo']);
    }

    public function testClientConvertByAdminWithValidParamsWithFixedAmount(): void
    {
        // Convert with valid input
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/convert_currency', [
            'clientId' => 3,
            'currencyFrom' => 'rub', // 25550
            'currencyTo' => 'usd',   // 3500
            'amount' => 1800,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(2, $response['result']);
        self::assertEquals(23750, $response['result'][0]['amount']);
        self::assertEquals('rub', $response['result'][0]['currency']);
        self::assertEquals(3520, $response['result'][1]['amount']);
        self::assertEquals('usd', $response['result'][1]['currency']);
        self::assertNull($response['error']);
    }

    public function testClientConvertByAdminWithValidParamsFullAmount(): void
    {
        // Convert with valid input and full amount
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/convert_currency', [
            'clientId' => 4,
            'currencyFrom' => 'eur', // 750
            'currencyTo' => 'rub',   // 2500
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(2, $response['result']);
        self::assertEquals(0, $response['result'][0]['amount']);
        self::assertEquals('eur', $response['result'][0]['currency']);
        self::assertEquals(85000, $response['result'][1]['amount']);
        self::assertEquals('rub', $response['result'][1]['currency']);
        self::assertNull($response['error']);
    }

    public function testClientConvertByAdminWithOneNotExistingAccount(): void
    {
        // Convert with valid input and full amount by admin, but only one account exist
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/convert_currency', [
            'clientId' => 1,
            'currencyFrom' => 'rub', // 10000
            'currencyTo' => 'usd',   // account with rub not exist for this client
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testClientConvertByAdminWithNotExistingAmount(): void
    {
        // Convert with valid input and full amount by admin, but only one account exist
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/convert_currency', [
            'clientId' => 4,
            'currencyFrom' => 'usd', // 650
            'currencyTo' => 'rub',   // 85000
            'amount' => 1000,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testClientConvertByManagerWithValidParamsWithOwnClient(): void
    {
        // Convert with valid input by manager with related client and accounts
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/convert_currency', [
            'clientId' => 2,
            'currencyFrom' => 'usd', // 2500
            'currencyTo' => 'eur',   // 1000
            'amount' => 100,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(2, $response['result']);
        self::assertEquals(2400, $response['result'][0]['amount']);
        self::assertEquals('usd', $response['result'][0]['currency']);
        self::assertEquals(1080, $response['result'][1]['amount']);
        self::assertEquals('eur', $response['result'][1]['currency']);
        self::assertNull($response['error']);
    }

    public function testClientConvertByManagerWithValidParamsWithWrongClient(): void
    {
        // Convert with valid input by manager with not related client
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/convert_currency', [
            'clientId' => 4,
            'currencyFrom' => 'eur', // 750
            'currencyTo' => 'rub',   // 2500
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }
}

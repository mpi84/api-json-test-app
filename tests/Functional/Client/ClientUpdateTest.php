<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/Client
 */
class ClientUpdateTest extends ApiTestCase
{
    public function testClientUpdateNoToken(): void
    {
        self::$client->jsonRequest('POST', '/api/v1/client/update/1');
        self::assertResponseStatusCodeSame(401);
    }

    public function testClientUpdateByAdminWithoutId(): void
    {
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update without id
        $this->apiJsonRequest('POST', '/api/v1/client/update', [], $token);
        self::assertResponseStatusCodeSame(404);
    }

    public function testClientUpdateByAdminWithNotExistingIdWithoutParams(): void
    {
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update with not existing id without params
        $this->apiJsonRequest('POST', '/api/v1/client/update/999', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(1, $response['error']);
    }

    public function testClientUpdateByAdminWithNotExistingIdWithValidParams(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update with not existing id and with valid param
        $this->apiJsonRequest('POST', '/api/v1/client/update/999', [
            'email' => 'test-client@test.local',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testClientUpdateByAdminWithExistingIdWithValidParams(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        // Update with existing id and with valid param
        $this->apiJsonRequest('POST', '/api/v1/client/update/3', [
            'email' => 'test1@test.local',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(3, $response['result']);
        self::assertEquals('test1@test.local', $response['result']['email']);
        self::assertNull($response['error']);
    }

    public function testClientUpdateByWrongManagerWithExistingIdWithValidParams(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        // Update with existing id and with valid param
        // Client not related with current manager
        $this->apiJsonRequest('POST', '/api/v1/client/update/5', [
            'email' => 'test2@test.local',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertNull($response['error']);
    }

    public function testClientUpdateByManagerWithExistingIdWithValidParams(): void
    {
        $token =  $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        // Update with existing id and with valid param with current manager
        // Client related with current manager
        $this->apiJsonRequest('POST', '/api/v1/client/update/3', [
            'email' => 'test2@test.local',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(3, $response['result']);
        self::assertEquals(2, $response['result']['managerId']);
        self::assertEquals('test2@test.local', $response['result']['email']);
        self::assertNull($response['error']);
    }
}

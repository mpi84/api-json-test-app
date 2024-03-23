<?php

namespace App\Tests\Functional\User;

use App\Entity\AppUser;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/Client
 */
class ClientCreateTest extends ApiTestCase
{
    public function testClientCreateNoToken(): void
    {
        self::$client->jsonRequest('POST', '/api/v1/client/create');
        self::assertResponseStatusCodeSame(401);
    }

    public function testClientCreateByAdminWithEmptyParams(): void
    {
        // Create with empty input
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/create', [], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(1, $response['error']);
    }

    public function testClientCreateByAdminWithInvalidParams(): void
    {
        // Create with 1 invalid params
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/create', [
            'email' => '123123123',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['result']);
        self::assertIsArray($response['error']);
        self::assertCount(1, $response['error']);
    }

    public function testClientCreateByAdminWithValidParams(): void
    {
        // Create with valid params by admin with custom manager id
        $token = $this->obtainUserToken(AppUser::USER_ADMIN_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/create', [
            'email' => 'test-client@test.local',
            'managerId' => 4,
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertNull($response['error']);
        self::assertIsArray($response['result']);
        self::assertCount(3, $response['result']);
        self::assertEquals('test-client@test.local', $response['result']['email']);
    }

    public function testClientCreateByManagerWithValidParams(): void
    {
        // Create with valid params by manager
        $token = $this->obtainUserToken(AppUser::USER_MANAGER_ROLE);
        $this->apiJsonRequest('POST', '/api/v1/client/create', [
            'email' => 'test-client-2@test.local',
        ], $token);
        self::assertResponseStatusCodeSame(200);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertIsArray($response['result']);
        self::assertCount(3, $response['result']);
        self::assertEquals(2, $response['result']['managerId']);
        self::assertEquals('test-client-2@test.local', $response['result']['email']);
        self::assertNull($response['error']);
    }
}

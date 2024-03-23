<?php

namespace App\Tests\Functional\User;

use App\DataFixtures\AdminFixtures;
use App\Tests\Functional\ApiTestCase;

/**
 * @group Functional
 * @group Functional/User
 */
class UserAuthTest extends ApiTestCase
{
    public function testInvalidAuth(): void
    {
        self::$client->request('GET', '/api/v1/auth');
        self::assertResponseStatusCodeSame(401);
    }

    public function testInvalidJsonParamsAuth(): void
    {
        self::$client->jsonRequest('GET', '/api/v1/auth', [
            'login' => 'fake@test.local',
            'password' => '123456',
        ]);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertResponseStatusCodeSame(401);
        self::assertEquals('Invalid credentials.', $response['message'] ?? null);
    }

    public function testValidJsonParamsAuth(): void
    {
        self::$client->jsonRequest('GET', '/api/v1/auth', [
            'login' => AdminFixtures::USER_ADMIN_EMAIL,
            'password' => AdminFixtures::USER_ADMIN_PASSWORD,
        ]);
        $response = $this->getDataFromResponse(self::$client->getResponse());
        self::assertResponseStatusCodeSame(200);
        self::assertArrayHasKey('token', $response);
    }
}

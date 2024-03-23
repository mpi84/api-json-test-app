<?php

namespace App\Tests\Functional;

use App\DataFixtures\AdminFixtures;
use App\DataFixtures\ManagerFixtures;
use App\Entity\AppUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class ApiTestCase extends WebTestCase
{
    protected static $client;
    protected static $app;

    protected ?EntityManagerInterface $entityManager;

    public static function setUpBeforeClass(): void
    {
        self::ensureKernelShutdown();
        self::$client = static::createClient();
        $kernel = self::bootKernel();

        $app = new Application($kernel);
        $app->setAutoExit(false);

        $app->run(new StringInput('doctrine:schema:drop -e test --force --quiet'));
        $app->run(new StringInput('doctrine:database:create -e test --quiet'));
        $app->run(new StringInput('doctrine:schema:update -e test --force --quiet'));
        $app->run(new StringInput('doctrine:fixtures:load -e test -n --quiet'));
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::ensureKernelShutdown();
        self::$client = static::createClient();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function getDataFromResponse(Response $response): mixed
    {
        return \json_decode($response->getContent(), true) ?? null;
    }

    public function apiJsonRequest(string $method, string $uri, ?array $params = null, string $token = null): Crawler
    {
        return self::$client->jsonRequest($method, $uri, $params, $token ? ['HTTP_AUTHORIZATION' => 'Bearer ' . $token] : []);
    }

    public function obtainUserToken(string $role): string
    {
        if ($role === AppUser::USER_ADMIN_ROLE) {
            self::$client->jsonRequest('GET', '/api/v1/auth', [
                'login' => AdminFixtures::USER_ADMIN_EMAIL,
                'password' => AdminFixtures::USER_ADMIN_PASSWORD,
            ]);
        } else {
            self::$client->jsonRequest('GET', '/api/v1/auth', [
                'login' => ManagerFixtures::USER_MANAGER_EMAIL,
                'password' => ManagerFixtures::USER_MANAGER_PASSWORD,
            ]);
        }

        $response = $this->getDataFromResponse(self::$client->getResponse());

        return $response['token'];
    }
}
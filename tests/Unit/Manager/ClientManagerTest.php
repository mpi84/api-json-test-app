<?php

namespace App\Tests\Manager;

use App\DTO\ClientConvertCurrencyContext;
use App\DTO\ClientCreateContext;
use App\DTO\ClientUpdateContext;
use App\Entity\Account;
use App\Entity\Client;
use App\Entity\AppUser;
use App\Manager\ClientManager;
use App\Repository\AccountRepository;
use App\Repository\ClientRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 * @group Unit/Manager
 */
class ClientManagerTest extends TestCase
{
    public function testCreateClient(): void
    {
        $clientContext = $this->createMock(ClientCreateContext::class);
        $clientContext->email = 'test@test.local';
        $clientContext->managerId = 1;

        $user = $this->createMock(AppUser::class);
        $user->method('getId')->willReturn(1);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager
            ->method('getReference')
            ->with(AppUser::class, $clientContext->managerId)
            ->willReturn($user)
        ;

        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $clientManager = new ClientManager($entityManager);
        $client = $clientManager->createClient($clientContext);

        self::assertInstanceOf(Client::class, $client);
        self::assertEquals($clientContext->email, $client->getEmail());
        self::assertEquals($clientContext->managerId, $client->getManager()->getId());
    }

    public function testUpdateClient(): void
    {
        $clientId = 1;

        $clientContext = $this->createMock(ClientUpdateContext::class);
        $clientContext->email = 'test@test.local';
        $clientContext->managerId = 5;

        $user = $this->createMock(AppUser::class);
        $user->method('getId')->willReturn(5);

        $clientForUpdate = new Client();
        $clientForUpdate->setEmail('client@test.local');
        $clientForUpdate->setManager($user);

        $repository = $this->createMock(ClientRepository::class);
        $repository
            ->method('findOneById')
            ->with($clientId, $clientContext->managerId)
            ->willReturn($clientForUpdate)
        ;

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($repository);
        $entityManager
            ->method('getReference')
            ->with(AppUser::class, $clientContext->managerId)
            ->willReturn($user)
        ;

        $repository->expects(self::once())->method('findOneById')->with($clientId, $clientContext->managerId);
        $entityManager->expects(self::once())->method('flush');

        $clientManager = new ClientManager($entityManager);
        $client = $clientManager->updateClient($clientId, $clientContext, $clientContext->managerId);

        self::assertInstanceOf(Client::class, $client);
        self::assertEquals($clientContext->email, $client->getEmail());
        self::assertEquals($clientContext->managerId, $client->getManager()->getId());
    }

    public function testDeleteClient(): void
    {
        $userId = 1;
        $clientId = 5;
        $client = new Client();

        $repository = $this->createMock(ClientRepository::class);
        $repository
            ->method('findOneById')
            ->with($clientId, $userId)
            ->willReturn($client);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $repository->expects(self::once())->method('findOneById')->with($clientId, $userId);
        $entityManager->expects(self::once())->method('remove')->with($client);
        $entityManager->expects(self::once())->method('flush');

        $clientManager = new ClientManager($entityManager);
        $result = $clientManager->deleteClientById($clientId, $userId);

        self::assertEquals(true, $result);
    }

    public function testConvertCurrencyBetweenAccounts(): void
    {
        $context = $this->createMock(ClientConvertCurrencyContext::class);
        // Rate 90
        $context->currencyFrom = 'usd';
        $context->currencyTo = 'rub';
        // Full amount
        $context->amount = null;
        $context->clientId = 5;

        $result = $this->convertCurrency($context);

        self::assertIsArray($result);
        self::assertCount(2, $result);
        // 100 * 90 = 10000
        self::assertEquals($context->currencyFrom, $result[0]->getCurrency());
        self::assertEquals(0, $result[0]->getAmount());
        self::assertEquals( $context->currencyTo, $result[1]->getCurrency());
        self::assertEquals(10000, $result[1]->getAmount());
    }

    private function convertCurrency(ClientConvertCurrencyContext $context): array
    {
        $userId = 1;
        $clientId = $context->clientId;

        $accountFrom = new Account();
        $accountFrom->setCurrency(Account::CURRENCY_USD);
        $accountFrom->setAmount(100);
        $accountTo = new Account();
        $accountTo->setCurrency(Account::CURRENCY_RUB);
        $accountTo->setAmount(1000);

        $client = $this->createMock(Client::class);

        $repositoryClient = $this->createMock(ClientRepository::class);
        $repositoryClient
            ->method('findOneById')
            ->with($clientId, $userId)
            ->willReturn($client)
        ;

        $repositoryAccount = $this->createMock(AccountRepository::class);
        $repositoryAccount
            ->method('findByClientIdAndCurrency')
            ->willReturnCallback(
                static function ($id, $currency) use ($clientId, $accountTo, $accountFrom) {
                    if ($currency=== Account::CURRENCY_USD) {
                        return $accountFrom;
                    }

                    return $accountTo;
                }
            )
        ;

        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction');
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager
            ->expects(self::atLeast(2))
            ->method('getRepository')
            ->with(self::logicalOr(self::equalTo(Client::class), self::equalTo(Account::class)))
            ->willReturnCallback(
                static function ($result) use ($repositoryClient, $repositoryAccount) {
                    return ($result === Client::class) ? $repositoryClient : $repositoryAccount;
                }
            )
        ;

        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('commit');

        $repositoryClient
            ->expects(self::once())
            ->method('findOneById')
            ->with($clientId, $userId)
            ->willReturn($client)
        ;
        $repositoryAccount->expects(self::atLeast(2))->method('findByClientIdAndCurrency');
        $connection->expects(self::once())->method('beginTransaction');
        $entityManager->expects(self::once())->method('commit');
        $entityManager->expects(self::once())->method('flush');

        $clientManager = new ClientManager($entityManager);

        return $clientManager->convertClientCurrency($context, $userId);
    }
}

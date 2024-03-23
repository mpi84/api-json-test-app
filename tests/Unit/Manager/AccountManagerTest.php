<?php

namespace App\Tests\Manager;

use App\DTO\AccountCreateContext;
use App\DTO\AccountUpdateContext;
use App\Entity\Account;
use App\Entity\Client;
use App\Manager\AccountManager;
use App\Manager\ClientManager;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 * @group Unit/Manager
 */
class AccountManagerTest extends TestCase
{
    public function testCreateAccount(): void
    {
        $managerId = 5;

        $accountContext = $this->createMock(AccountCreateContext::class);
        $accountContext->currency = Account::CURRENCY_RUB;
        $accountContext->amount = 100;
        $accountContext->clientId = 10;

        $client = $this->createMock(Client::class);
        $client->method('getId')->willReturn($accountContext->clientId);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager
            ->method('getReference')
            ->with(Client::class, $accountContext->clientId)
            ->willReturn($client)
        ;

        $entityManager->expects(self::once())->method('getReference');
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $clientManager = $this->createMock(ClientManager::class);
        $clientManager
            ->expects(self::once())
            ->method('getClientById')
            ->with($accountContext->clientId, $managerId)
            ->willReturn($client);

        $accountManager = new AccountManager($entityManager, $clientManager);
        $account = $accountManager->createAccount($accountContext, $managerId);

        self::assertInstanceOf(Account::class, $account);
        self::assertEquals($accountContext->currency, $account->getCurrency());
        self::assertEquals($accountContext->amount, $account->getAmount());
        self::assertEquals($accountContext->clientId, $account->getClient()->getId());
    }

    public function testUpdateAccount(): void
    {
        $managerId = 5;
        $accountId = 10;
        $clientId = 7;

        $accountContext = $this->createMock(AccountUpdateContext::class);
        $accountContext->currency = Account::CURRENCY_RUB;
        $accountContext->amount = 1000;

        $client = $this->createMock(Client::class);
        $client->method('getId')->willReturn($clientId);

        $accountForUpdate = new Account();
        $accountForUpdate->setCurrency(Account::CURRENCY_RUB);
        $accountForUpdate->setAmount(50);
        $accountForUpdate->setClient($client);

        $repository = $this->createMock(AccountRepository::class);
        $repository
            ->method('findOneById')
            ->with($accountId, $managerId)
            ->willReturn($accountForUpdate)
        ;

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($repository);
        $entityManager
            ->method('getReference')
            ->with(Account::class)
            ->willReturn($repository)
        ;

        $repository->expects(self::once())->method('findOneById')->with($accountId, $managerId);
        $entityManager->expects(self::once())->method('flush');

        $clientManager = $this->createMock(ClientManager::class);

        $accountManager = new AccountManager($entityManager, $clientManager);
        $account = $accountManager->updateAccount($accountId, $accountContext, $managerId);

        self::assertInstanceOf(Account::class, $account);
        self::assertEquals($accountContext->currency, $account->getCurrency());
        self::assertEquals($accountContext->amount, $account->getAmount());
        self::assertEquals($clientId, $account->getClient()->getId());
    }

    public function testDeleteAccount(): void
    {
        $managerId = 5;
        $accountId = 10;
        $account = new Account();

        $repository = $this->createMock(AccountRepository::class);
        $repository
            ->method('findOneById')
            ->with($accountId, $managerId)
            ->willReturn($account)
        ;

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $repository->expects(self::once())->method('findOneById')->with($accountId, $managerId);
        $entityManager->expects(self::once())->method('remove')->with($account);
        $entityManager->expects(self::once())->method('flush');

        $clientManager = $this->createMock(ClientManager::class);

        $accountManager = new AccountManager($entityManager, $clientManager);
        $result = $accountManager->deleteAccountById($accountId, $managerId);

        self::assertEquals(true, $result);
    }
}

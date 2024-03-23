<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\AccountCreateContext;
use App\DTO\AccountUpdateContext;
use App\Entity\Account;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;

class AccountManager
{
    private EntityManagerInterface $entityManager;
    private ClientManager $clientManager;

    public function __construct(EntityManagerInterface $entityManager, ClientManager $clientManager)
    {
        $this->entityManager = $entityManager;
        $this->clientManager = $clientManager;
    }

    public function createAccount(AccountCreateContext $accountContext, ?int $managerId = null): ?Account
    {
        $account = null;

        $client = $this->clientManager->getClientById($accountContext->clientId, $managerId);

        if ($client) {
            $account = new Account();

            $account
                ->setCurrency($accountContext->currency)
                ->setAmount($accountContext->amount)
                ->setClient($this->entityManager->getReference(Client::class, $accountContext->clientId));

            $this->entityManager->persist($account);
            $this->entityManager->flush();
        }

        return $account;
    }

    public function updateAccount(int $id, AccountUpdateContext $accountContext, ?int $managerId = null): ?Account
    {
        $account = $this->getAccountById($id, $managerId);

        if ($account) {
            $needUpdate = false;

            if ($accountContext->currency && $accountContext->currency !== $account->getCurrency()) {
                $account->setCurrency($accountContext->currency);

                $needUpdate = true;
            }

            if ($accountContext->amount !== null && $accountContext->amount !== $account->getAmount()) {
                $account->setAmount($accountContext->amount);

                $needUpdate = true;
            }

            if ($needUpdate) {
                $this->entityManager->flush();

                return $account;
            }
        }

        return null;
    }

    public function getAccountById(int $accountId, ?int $managerId = null): ?Account
    {
        return $this->entityManager
            ->getRepository(Account::class)
            ->findOneById($accountId, $managerId)
        ;
    }

    public function getAllAccountsByManagerId(int $managerId): array
    {
        return $this->entityManager
            ->getRepository(Account::class)
            ->findAllByManagerId($managerId)
        ;
    }

    public function getAllAccounts(): array
    {
        return $this->entityManager
            ->getRepository(Account::class)
            ->findBy([], ['id' => 'DESC', 'client' => 'DESC'])
        ;
    }

    public function deleteAccountById(int $id, ?int $managerId = null): ?bool
    {
        $account = $this->getAccountById($id, $managerId);

        if ($account) {
            $this->entityManager->remove($account);
            $this->entityManager->flush();

            $result = true;
        }

        return $result ?? null;
    }
}

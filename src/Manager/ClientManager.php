<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\ClientConvertCurrencyContext;
use App\DTO\ClientCreateContext;
use App\DTO\ClientUpdateContext;
use App\Entity\Account;
use App\Entity\Client;
use App\Entity\AppUser;
use App\Helpers\CurrencyConverterHelper;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class ClientManager
{
    use CurrencyConverterHelper;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createClient(ClientCreateContext $clientContext): Client
    {
        $client = new Client();

        $client
            ->setEmail($clientContext->email)
            ->setManager($this->entityManager->getReference(AppUser::class, $clientContext->managerId))
        ;

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $client;
    }

    public function updateClient(int $id, ClientUpdateContext $clientContext, ?int $managerId = null): ?Client
    {
        $client = $this->getClientById($id, $managerId);

        if ($client) {
            $needUpdate = false;

            if ($clientContext->email && $clientContext->email !== $client->getEmail()) {
                $client->setEmail($clientContext->email);

                $needUpdate = true;
            }

            if ($clientContext->managerId && $clientContext->managerId !== $client->getManager()->getId()) {
                $client->setManager($this->entityManager->getReference(AppUser::class, $clientContext->managerId));

                $needUpdate = true;
            }

            if ($needUpdate) {
                $this->entityManager->flush();

                return $client;
            }
        }

        return null;
    }

    public function getClientById(int $clientId, ?int $managerId = null): ?Client
    {
        return $this->entityManager
            ->getRepository(Client::class)
            ->findOneById($clientId, $managerId)
        ;
    }

    public function getAllClientsByManagerId(int $managerId): array
    {
        return $this->entityManager
            ->getRepository(Client::class)
            ->findAllByManagerId($managerId)
        ;
    }

    public function getAllClients(): array
    {
        return $this->entityManager
            ->getRepository(Client::class)
            ->findAll()
        ;
    }

    public function deleteClientById(int $id, ?int $managerId = null): ?bool
    {
        $client = $this->getClientById($id, $managerId);

        if ($client) {
            $this->entityManager->remove($client);
            $this->entityManager->flush();

            $result = true;
        }

        return $result ?? null;
    }

    public function convertClientCurrency(ClientConvertCurrencyContext $context, ?int $managerId = null): ?array
    {
        $client = $this->getClientById($context->clientId, $managerId);

        if ($client) {
            $currencyFrom = $this->entityManager
                ->getRepository(Account::class)
                ->findByClientIdAndCurrency($context->clientId, $context->currencyFrom)
            ;

            if ($currencyFrom && $currencyFrom->getAmount() > 0) {
                $amountToConvert = $currencyFrom->getAmount();
                $amountCurrencyFromLeft = 0;

                if ($context->amount) {
                    if (($currencyFrom->getAmount() - $context->amount) < 0) {
                        return null;
                    }

                    $amountToConvert = $context->amount;
                    $amountCurrencyFromLeft = $currencyFrom->getAmount() - $context->amount;
                }


                $currencyTo = $this->entityManager
                    ->getRepository(Account::class)
                    ->findByClientIdAndCurrency($context->clientId, $context->currencyTo)
                ;


                $this->entityManager->getConnection()->beginTransaction();
                try {
                    if ($currencyTo) {
                        $convertedAmount = $this->convertBetweenTwoCurrencies(
                            $context->currencyFrom,
                            $context->currencyTo,
                            $amountToConvert
                        );

                        $currencyFrom->setAmount($amountCurrencyFromLeft);
                        $currencyTo->setAmount($currencyTo->getAmount() + $convertedAmount);

                        $this->entityManager->flush();

                        $this->entityManager->commit();

                        return [$currencyFrom, $currencyTo];
                    }
                } catch (\Exception $exception) {
                    $this->entityManager->rollback();

                    throw new Exception('Convert transaction failed: ' . $exception->getMessage());
                }
            }
        }

        return null;
    }
}

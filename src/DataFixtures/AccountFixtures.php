<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AccountFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Client 1
        $account11 = (new Account())
            ->setCurrency(Account::CURRENCY_RUB)
            ->setAmount(10000)
            ->setClient($this->getReference(ClientFixtures::CLIENT_REFERENCE . '-1', Client::class));
        $manager->persist($account11);
        // Client 2
        $account21 = (new Account())
            ->setCurrency(Account::CURRENCY_EUR)
            ->setAmount(1000)
            ->setClient($this->getReference(ClientFixtures::CLIENT_REFERENCE . '-2', Client::class));
        $manager->persist($account21);
        $account22 = (new Account())
            ->setCurrency(Account::CURRENCY_USD)
            ->setAmount(2500)
            ->setClient($this->getReference(ClientFixtures::CLIENT_REFERENCE . '-2', Client::class));
        $manager->persist($account22);
        // Client 3
        $account31 = (new Account())
            ->setCurrency(Account::CURRENCY_USD)
            ->setAmount(3500)
            ->setClient($this->getReference(ClientFixtures::CLIENT_REFERENCE . '-3', Client::class));
        $manager->persist($account31);
        $account32 = (new Account())
            ->setCurrency(Account::CURRENCY_RUB)
            ->setAmount(25550)
            ->setClient($this->getReference(ClientFixtures::CLIENT_REFERENCE . '-3', Client::class));
        $manager->persist($account32);
        // Client 4
        $account41 = (new Account())
            ->setCurrency(Account::CURRENCY_RUB)
            ->setAmount(2500)
            ->setClient($this->getReference(ClientFixtures::CLIENT_REFERENCE . '-4', Client::class));
        $manager->persist($account41);
        $account42 = (new Account())
            ->setCurrency(Account::CURRENCY_EUR)
            ->setAmount(750)
            ->setClient($this->getReference(ClientFixtures::CLIENT_REFERENCE . '-4', Client::class));
        $manager->persist($account42);
        $account43 = (new Account())
            ->setCurrency(Account::CURRENCY_USD)
            ->setAmount(650)
            ->setClient($this->getReference(ClientFixtures::CLIENT_REFERENCE . '-4', Client::class));
        $manager->persist($account43);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
        ];
    }
}

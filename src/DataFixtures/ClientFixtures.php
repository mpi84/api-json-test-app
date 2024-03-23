<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\AppUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture implements DependentFixtureInterface
{
    public const CLIENT_REFERENCE = 'client';

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i < 6; $i++) {
            $client = new Client();
            $client
                ->setEmail("client{$i}@test.local")
                ->setManager(
                    ($i < 4) ?
                        $this->getReference(ManagerFixtures::USER_MANAGER_1, AppUser::class)
                        :
                        $this->getReference(ManagerFixtures::USER_MANAGER_2, AppUser::class)
                )
            ;

            $manager->persist($client);

            $this->addReference(self::CLIENT_REFERENCE . "-{$i}", $client);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ManagerFixtures::class,
        ];
    }
}

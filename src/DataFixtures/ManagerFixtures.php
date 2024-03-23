<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AppUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ManagerFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_MANAGER_REFERENCE = 'manager';
    public const USER_MANAGER_1 = 'manager-1';
    public const USER_MANAGER_2 = 'manager-2';

    public const USER_MANAGER_EMAIL = 'manager1@test.local';
    public const USER_MANAGER_PASSWORD = '123456';

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i < 4; $i++) {
            $user = new AppUser();
            $user
                ->setEmail("manager{$i}@test.local")
                ->setRoles([AppUser::USER_MANAGER_ROLE])
                ->setPassword($this->hasher->hashPassword($user, '123456'));

            $manager->persist($user);

            $this->addReference(self::USER_MANAGER_REFERENCE . "-{$i}", $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AdminFixtures::class,
        ];
    }
}

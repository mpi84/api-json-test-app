<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AppUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    public const USER_ADMIN_REFERENCE = 'admin';
    public const USER_ADMIN_EMAIL = 'admin@test.local';
    public const USER_ADMIN_PASSWORD = '123456';

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new AppUser();
        $user
            ->setEmail(self::USER_ADMIN_EMAIL)
            ->setRoles([AppUser::USER_ADMIN_ROLE])
            ->setPassword($this->hasher->hashPassword($user, self::USER_ADMIN_PASSWORD));

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER_ADMIN_REFERENCE, $user);
    }
}

<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\UserCreateContext;
use App\DTO\UserUpdateContext;
use App\Entity\AppUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $hasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher)
    {
        $this->entityManager = $entityManager;
        $this->hasher = $hasher;
    }

    public function createUser(UserCreateContext $userContext): AppUser
    {
        $user = new AppUser();

        $user
            ->setEmail($userContext->email)
            ->setRoles((array) $userContext->role)
            ->setPassword($this->hasher->hashPassword($user, $userContext->password))
        ;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function updateUser(int $id, UserUpdateContext $userContext): ?AppUser
    {
        $user = $this->getUserById($id, true);

        if ($user) {
            $needUpdate = false;

            if ($userContext->email && $userContext->email !== $user->getEmail()) {
                $user->setEmail($userContext->email);

                $needUpdate = true;
            }
            if ($userContext->role && !in_array($userContext->role, $user->getRoles(), true)) {
                $user->setRoles((array) $userContext->role);

                $needUpdate = true;
            }
            if ($userContext->password) {
                $hashedPassword = $this->hasher->hashPassword($user, $userContext->password);

                if ($hashedPassword !== $user->getPassword()) {
                    $user->setPassword($hashedPassword);

                    $needUpdate = true;
                }
            }
            if ($needUpdate) {
                $this->entityManager->flush();

                return $user;
            }
        }

        return null;
    }

    public function getUserById(int $id, bool $byAdmin = false): ?AppUser
    {
        return $this->entityManager
            ->getRepository(AppUser::class)
            ->findUserById($id, $byAdmin)
        ;
    }

    public function getAllUsers(bool $byAdmin = false): array
    {
        return $this->entityManager
            ->getRepository(AppUser::class)
            ->findAllByUser($byAdmin)
        ;
    }

    public function deleteUserById(int $id): ?bool
    {
        $user = $this->getUserById($id, true);

        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            $result = true;
        }

        return $result ?? null;
    }
}

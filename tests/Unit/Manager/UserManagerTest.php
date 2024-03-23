<?php

namespace App\Tests\Manager;

use App\DTO\UserCreateContext;
use App\DTO\UserUpdateContext;
use App\Entity\AppUser;
use App\Manager\UserManager;
use App\Repository\AppUserRepository;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

/**
 * @group Unit
 * @group Unit/Manager
 */
class UserManagerTest extends TestCase
{
    public function testCreateUser(): void
    {
        $userContext = $this->createMock(UserCreateContext::class);
        $userContext->role = AppUser::USER_MANAGER_ROLE;
        $userContext->email = 'test@test.local';
        $userContext->password = '123123';

        $hashedPassword = 'aaaa';

        $entityManager = $this->createMock(EntityManager::class);

        $hasher = $this->createMock(UserPasswordHasher::class);
        $hasher->method('hashPassword')->willReturn($hashedPassword);

        $hasher->expects(self::once())->method('hashPassword');
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $userManager = new UserManager($entityManager, $hasher);
        $user = $userManager->createUser($userContext);

        self::assertInstanceOf(AppUser::class, $user);
        self::assertEquals(true, in_array(AppUser::USER_MANAGER_ROLE, $user->getRoles(), true));
        self::assertEquals($userContext->email, $user->getEmail());
        self::assertEquals($hashedPassword, $user->getPassword());
    }

    public function testUpdateUser(): void
    {
        $userId = 1;
        $hashedPassword = 'aaaa';
        $hashedPasswordNew = 'bbbb';

        $userForUpdate = new AppUser();
        $userForUpdate->setEmail('admin@test.local');
        $userForUpdate->setRoles([AppUser::USER_ADMIN_ROLE]);
        $userForUpdate->setPassword($hashedPassword);

        $userContext = $this->createMock(UserUpdateContext::class);
        $userContext->role = AppUser::USER_MANAGER_ROLE;
        $userContext->email = 'test@test.local';
        $userContext->password = '123123';

        $repository = $this->createMock(AppUserRepository::class);
        $repository
            ->method('findUserById')
            ->with($userId, true)
            ->willReturn($userForUpdate);
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $hasher = $this->createMock(UserPasswordHasher::class);
        $hasher->method('hashPassword')->willReturn($hashedPasswordNew);

        $hasher->expects(self::once())->method('hashPassword');
        $repository->expects(self::once())->method('findUserById')->with($userId, true);
        $entityManager->expects(self::once())->method('flush');

        $userManager = new UserManager($entityManager, $hasher);
        $updatedUser = $userManager->updateUser($userId, $userContext);

        self::assertEquals(true, in_array($userContext->role, $updatedUser->getRoles(), true));
        self::assertEquals($userContext->email, $updatedUser->getEmail(),);
        self::assertEquals($hashedPasswordNew, $updatedUser->getPassword());
    }

    public function testDeleteUser(): void
    {
        $userId = 1;
        $user = new AppUser();

        $repository = $this->createMock(AppUserRepository::class);
        $repository
            ->method('findUserById')
            ->with($userId, true)
            ->willReturn($user);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $entityManager->expects(self::once())->method('remove')->with($user);
        $entityManager->expects(self::once())->method('flush');

        $hasher = $this->createMock(UserPasswordHasher::class);

        $hasher->expects(self::never())->method('hashPassword');
        $userManager = new UserManager($entityManager, $hasher);
        $result = $userManager->deleteUserById($userId);

        self::assertEquals(true, $result);
    }
}

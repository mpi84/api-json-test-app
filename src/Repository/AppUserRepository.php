<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AppUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<AppUser>
 *
 * @implements PasswordUpgraderInterface<AppUser>
 *
 * @method AppUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppUser[]    findAll()
 * @method AppUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppUserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppUser::class);
    }

    public function findUserById(int $userId, bool $byAdmin = false): ?AppUser
    {
        if ($byAdmin) {
            return $this->createQueryBuilder('u')
                ->andWhere('u.id = :id')
                ->setParameter('id', $userId)
                ->orderBy('u.id', 'DESC')
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(AppUser::class, 'u');

        return $this
            ->getEntityManager()
            ->createNativeQuery($this->getNativeSqlForFindUserByIdAndByManager($userId), $rsm)
            ->setParameter('userId', $userId)
            ->getOneOrNullResult()
        ;
    }

    public function findAllByUser(bool $byAdmin = false): array
    {
        if ($byAdmin) {
            return $this->createQueryBuilder('u')
                ->orderBy('u.id', 'DESC')
                ->getQuery()
                ->getResult()
            ;
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(AppUser::class, 'u');

        return $this
            ->getEntityManager()
            ->createNativeQuery($this->getNativeSqlForFindAllUsersByManager(), $rsm)
            ->getArrayResult()
        ;
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof AppUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    private function getManagerRole(): string
    {
        return AppUser::USER_MANAGER_ROLE;
    }

    private function getNativeSqlForFindUserByIdAndByManager(int $id): string
    {
//<<<MYSQL
//SELECT u.id, u.email, u.roles
//FROM app_user u
//WHERE JSON_CONTAINS(u.roles, '"{$this->getManagerRole()}"') = 1 AND u.id = :userId
//MYSQL;

        return <<<PGSQL
SELECT u.id, u.email, u.roles
FROM app_user u
WHERE (u.roles)::jsonb ?? '{$this->getManagerRole()}' AND u.id = :userId
PGSQL;
    }

    private function getNativeSqlForFindAllUsersByManager(): string
    {
//<<<MYSQL
//SELECT u.id, u.email, u.roles
//FROM app_user u
//WHERE JSON_CONTAINS(u.roles, '"{$this->getManagerRole()}"') = 1
//ORDER BY u.id
//MYSQL;
        return <<<PGSQL
SELECT u.id, u.email, u.roles
FROM app_user u
WHERE (u.roles)::jsonb ?? '{$this->getManagerRole()}'
ORDER BY u.id
PGSQL;
    }
}

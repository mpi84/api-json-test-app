<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 *
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('a')
        ->orderBy('a.id, a.client_id', 'DESC')
        ->getQuery()
        ->getResult()
        ;
    }

    public function findAllByManagerId(?int $managerId): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.client', 'c')
            ->andWhere('IDENTITY(c.manager) = :managerId')
            ->setParameter('managerId', $managerId)
            ->orderBy('a.id', 'DESC')
            ->orderBy('a.client', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneById(int $accountId, ?int $managerId = null): ?Account
    {
        $queryBuilder = $this->createQueryBuilder('a');

        if ($managerId) {
            $queryBuilder
                ->innerJoin('a.client', 'c')
                ->andWhere('IDENTITY(c.manager) = :managerId')
                ->setParameter('managerId', $managerId)
            ;
        }

        return $queryBuilder
            ->andWhere('a.id = :id')
            ->setParameter('id', $accountId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByClientIdAndCurrency(int $clientId, string $currency): ?Account
    {
        return $this->createQueryBuilder('a')
            ->andWhere('IDENTITY(a.client) = :clientId')
            ->andWhere('a.currency = :currency')
            ->setParameter('clientId', $clientId)
            ->setParameter('currency', $currency)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 *
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findAllByManagerId(int $managerId): array
    {
                return $this->createQueryBuilder('c')
                    ->andWhere('IDENTITY(c.manager) = :managerId')
                    ->setParameter('managerId', $managerId)
                    ->orderBy('c.id', 'DESC')
                    ->getQuery()
                    ->getResult()
                ;
    }

    public function findOneById(int $clientId, ?int $managerId = null): ?Client
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $clientId)
        ;

        if ($managerId) {
            $queryBuilder
                ->andWhere('IDENTITY(c.manager) = :managerId')
                ->setParameter('managerId', $managerId)
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

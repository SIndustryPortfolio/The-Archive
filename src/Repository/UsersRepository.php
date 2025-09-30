<?php

namespace App\Repository;

use App\Entity\Credentials;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

//    /**
//     * @return Credentials[] Returns an array of Credentials objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function findByApiToken($apiToken)
    {
        return $this->createQueryBuilder('c')
            ->where('c.apiToken = :token')
            ->setParameter('token', $apiToken)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findUserByUserId($iD) : ?User
    {
        return $this->createQueryBuilder('c')
        ->andWhere('c.iD = :iD')
        ->setParameter('iD', $iD)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findUserByUsername($value): ?User
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.username = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

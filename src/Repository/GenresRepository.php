<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Credentials;
use App\Entity\Genre;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\UserType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Genre>
 */
class GenresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
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

    public function findGenreById($iD) : ?Genre
    {
        return $this->createQueryBuilder('c')
            ->where('c.iD = :iD')
            ->setParameter('iD', $iD)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findGenreByName($name): ?Genre
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name = :name')
            ->setParameter("name", $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

<?php

namespace App\Repository;

use App\Entity\Credentials;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\UserType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
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

    public function findReviewsByBook($book)
    {
        return $this->createQueryBuilder('c')
            ->where('c.book = :book')
            ->setParameter('book', $book)
            ->getQuery()
            ->getResult();
    }

    public function getAverageRating($book)
    {
        return $this->createQueryBuilder('c')
            ->select('AVG(c.rating)')
            ->where("c.book = :book")
            ->setParameter("book", $book)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findReviewById(int $iD) : ?Review
    {
        return $this->createQueryBuilder('c')
        ->where('c.iD = :iD')
        ->setParameter('iD', $iD)
        ->getQuery()
        ->getOneOrNullResult();
    }

    public function findReview($book, $user): ?Review
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.book = :book')
            ->setParameter("book", $book)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

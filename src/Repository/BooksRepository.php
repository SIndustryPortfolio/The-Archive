<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Credentials;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\UserType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BooksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
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

    public function findAllBooks() : array
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->getQuery()
            ->getResult();
    }

    public function findBooksByGenre($genre) : array
    {
        return $this->createQueryBuilder('c')
            ->where('c.genre = :genre')
            ->setParameter('genre', $genre)
            ->getQuery()
            ->getResult();
    }

    public function findBookById($bookId) : ?Book
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.iD = :bookId')
            ->setParameter('bookId', $bookId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBookByTitle($bookTitle): ?Book
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.title = :bookTitle')
            ->setParameter("bookTitle", $bookTitle)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

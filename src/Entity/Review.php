<?php

namespace App\Entity;

use App\Repository\ReviewsRepository;
use App\Repository\UserTypesRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="Review")
 */
#[ORM\Entity(repositoryClass: ReviewsRepository::class)]
#[ORM\Table(name: 'Review')]
class Review
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'iD', type: 'integer', nullable: false)]
    private $iD;


    #[ORM\ManyToOne(targetEntity: User::class, cascade: ["persist"], inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: "user", referencedColumnName: "iD", nullable: false)]
    private $user;


    #[ORM\ManyToOne(targetEntity: Book::class, cascade: ["persist"], inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: "book", referencedColumnName: "iD", nullable: false)]
    private $book;


    #[ORM\Column(name: 'content', length: 10000)]
    private $content;


    #[ORM\Column(name: 'rating', type: 'integer')]
    private $rating;

    // Functions
    function getDict() : array
    {
        return array(
            "iD" => $this->iD,
            "user" => $this->user->getId(),
            "book" => $this->book->getId(),
            "content" => $this->content,
            "rating" => $this->rating
        );
    }

    // Mutators
    function setId($_iD) : void
    {
        $this->iD = $_iD;
    }

    function setRating($_Rating) : void
    {
        $this->rating = $_Rating;
    }

    function setBook(Book $_Book) : void
    {
        $this->book = $_Book;
    }

    function setContent($_Content) : void
    {
        $this->content = $_Content;
    }

    function setUser(User $_User) : void
    {
        $this->user = $_User;
    }

    // ACCESSORS
    function getRating() : int
    {
        return $this->rating;
    }

    function getBook() : Book
    {
        return $this->book;
    }

    function getUser() : User
    {
        return $this->user;
    }

    function getId() : int
    {
        return $this->iD;
    }

    function getContent() : string
    {
        return $this->content;
    }
}
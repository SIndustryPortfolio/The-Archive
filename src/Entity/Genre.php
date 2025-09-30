<?php

namespace App\Entity;

use App\Repository\GenresRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: GenresRepository::class)]
#[ORM\Table(name: 'Genre')]
class Genre
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "iD", type: 'integer', nullable: false)]
    private $iD;

    #[ORM\Column(name: 'name', length: 255, nullable: false)]
    private $name;

    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: "genre")]
    private $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    // Accessors
    public function getId() : int
    {
        return $this->iD;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBooks()
    {
        return $this->books;
    }

    // Mutators
    public function setId(int $_Id) : void
    {
        $this->iD = $_Id;
    }

    public function setName(string $_Name): void
    {
        $this->name = $_Name;
    }

    public function setBooks($_Books) : void
    {
        $this->books = $_Books;
    }
}
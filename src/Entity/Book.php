<?php

namespace App\Entity;

use App\Repository\BooksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\BookCredentialsRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="Book")
 */
#[ORM\Entity(repositoryClass: BooksRepository::class)]
#[ORM\Table(name: 'Book')]
class Book
{
    /**
     * @ORM\Id
     * @ORM\Column(type="Integer")
     * @ORM\GeneratedValue(strategy="AUTO)
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "iD", type: 'integer', nullable: false)]
    private $iD;

    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: false)]
    private $title;

    /**
     * @ORM\Column(type="int")
     */
    #[ORM\Column(name: 'author', length: 255)]
    private $author;


    #[ORM\ManyToOne(targetEntity: Genre::class, cascade: ["persist"], inversedBy: 'books')]
    #[ORM\JoinColumn(name: "genre", referencedColumnName: "iD", nullable: false)]
    private $genre;

    /**
     * @ORM\Column(type="string")
     */
    #[ORM\Column(name: 'imageURL', length: 500)]
    private $imageURL;

    #[ORM\Column(name: 'description', length: 1000)]
    private $description;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: "book")]
    private $reviews;

    private $isExternal;

    // Functions
    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->isExternal = false;
    }

    public function getDict()
    {
        return array(
            "iD" => $this->iD ?? -1,
            "title" => $this->title,
            "author" => $this->author,
            "genre" => $this->genre->getName(),
            "imageURL" => $this->imageURL,
            "description" => $this->description,
            "isExternal" => $this->isExternal
        );
    }

    // Mutators
    function setIsExternal(bool $_IsExternal) : void
    {
        $this->isExternal = $_IsExternal;
    }

    function setId($_Id) : void
    {
        $this->iD = $_Id;
    }

    function setGenre($_Genre) : void
    {
        $this->genre = $_Genre;
    }

    function setTitle(string $_Title) : void
    {
        $this->title = $_Title;
    }

    function setAuthor(string $_Author) : void
    {
        $this->author = $_Author;
    }

    function setImageURL(string $_ImageURL) : void
    {
        $this->imageURL = $_ImageURL;
    }

    function addReview(Review $_Review) : void
    {
        $this->reviews[] = $_Review;
    }

    function setDescription(string $_Description) : void
    {
        $this->description = $_Description;
    }

    // Accessors
    function getId() : int {
        return $this->iD ?? -1;
    }

    function getDescription() : string
    {
        return $this->description;
    }

    function getTitle() : string
    {
        return $this->title;
    }

    function getIsExternal() : bool
    {
        return $this->isExternal ?? false;
    }

    function getAuthor() : string
    {
        return $this->author;
    }

    function getImageURL() : string
    {
        return str_replace(" ", "%20", $this->imageURL);
    }

    function getGenre() : Genre
    {
        return $this->genre;
    }

    //#@return Collection|Review[]
    function getReviews()
    {

        if ($this->reviews instanceof PersistentCollection && !$this->reviews->isInitialized()) {
            $this->reviews->initialize();
        }

        return $this->reviews;
    }
}
<?php

namespace App\Entity;

use AllowDynamicProperties;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserTypesRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="User")
 */
#[AllowDynamicProperties] #[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'User')]
class User extends UserTypesRepository implements UserInterface, PasswordAuthenticatedUserInterface
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

    #[ORM\Column(name: 'username', type: 'string', length: 36, nullable: false)]
    private $username;

    /**
     * @ORM\Column(type="string")
     */
    #[ORM\Column(name: 'password', length: 255, nullable: false)]
    private $password;


    /**
     * @ORM\OneToMany(targetEntity="Review", mappedBy="user")
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: "User")]
    private $reviews;

    #[ORM\Column(name: 'profilePictureURL', length: 500)]
    private $profilePictureURL;


    //#[ORM\ManyToOne(targetEntity: UserType::class, inversedBy: "iD")]
    //#[ORM\JoinColumn(name: "userType", referencedColumnName: "iD", nullable: false)]

    #[ORM\ManyToOne(targetEntity: UserType::class, cascade: ["persist"], fetch: "EAGER", inversedBy: 'users')]
    #[ORM\JoinColumn(name: "userType", referencedColumnName: "iD", nullable: false)]
    private $userType;


    #[ORM\Column(name: 'apiToken', type: 'string', length: 255, nullable: true)]
    private $apiToken;

    // Functions
    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    // Mutators
    function setAPIToken($_apiToken) : void
    {
        $this->apiToken = $_apiToken;
    }

    function setId(int $_Id) : void
    {
        $this->iD = $_Id;
    }

    function setProfilePictureURL(string $_ProfilePictureURL) : void
    {
        $this->profilePictureURL = $_ProfilePictureURL;
    }
    function setUsername(string $_Username) : void
    {
        $this->username = $_Username;
    }

    function setPassword(string $_Password) : void
    {
        $this->password = $_Password;
    }

    function setUserType(/**int $_UserType**/ UserType $_UserType) : void
    {
        $this->userType = $_UserType;
    }

    function addReview (Review $_Review) : void
    {
        $this->reviews[] = $_Review;
    }

    // Accessors
    /**function getId() : string
    {
        return $this->username;
    }**/

    function getApiToken() // CAN BE NULL!
    {
        return $this->apiToken;
    }

    function getId() : int
    {
        return $this->iD;
    }

    function getProfilePictureURL() : string
    {
        return $this->profilePictureURL;
    }

    function getUsername() : string
    {
        return $this->username;
    }

    function getPassword() : string
    {
        return $this->password;
    }

    function getUserType() : UserType //int
    {
        return $this->userType;
    }

    #@return Collection|Review[]
    function getReviews() : ArrayCollection
    {
        return $this->reviews;
    }

    public function getRoles(): array
    {
        // TODO: Implement getRoles() method.
        return ["ROLE_USER", $this->userType->getRole()];
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
        $this->password = null;
        $this->username = null;
    }

    public function getUserIdentifier(): string
    {
        // TODO: Implement getUserIdentifier() method.
        return $this->username;
    }
}
<?php

namespace App\Entity;

use App\Repository\UserTypesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="UserType")
 */
#[ORM\Entity(repositoryClass: UserTypesRepository::class)]
#[ORM\Table(name: 'UserType')]
class UserType
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Column(name: "iD", type: "integer", nullable: false)]
    //#[ORM\OneToMany(targetEntity: User::class, mappedBy: 'userType')]
    private $iD;

    #[ORM\Column(name: 'name', type: 'string', length: 100, nullable: false)]
    private $name;


    #[ORM\Column(name: 'level', type: 'integer', nullable: false)]
    private $level;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: "username")]
    private $users;


    // Functions
    // ACCESSORS
    public function getId() : int
    {
        return $this->iD;
    }
    public function getName(): string
    {
        return $this->name;
    }

    public function getLevel() : int
    {
        return $this->level;
    }

    // MUTATORS
    public function setId(int $_Id) : void
    {
        $this->iD = $_Id;
    }

    public function setName(string $_Name) : void
    {
        $this->name = $_Name;
    }

    public function setLevel(int $_Level) : void
    {
        $this->level = $_Level;
    }
}
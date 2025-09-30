<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Entity\UserType;
use App\Repository\GenresRepository;
use App\Repository\UserTypesRepository;
use App\Service\UtilitiesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private $utilitiesService;
    private $userTypesRepository;
    private $genresRepository;


    function __construct(EntityManagerInterface $entityManager, UserTypesRepository $userTypesRepository, UtilitiesService $utilitiesService, GenresRepository $genresRepository)
    {
        $this->utilitiesService = $utilitiesService; //new UtilitiesService();
        $this->userTypesRepository = $userTypesRepository;
        $this->genresRepository = $genresRepository;


        foreach($this->utilitiesService->getGenres() as $GenreName => $GenreInfo) // Auto fill DB with Genres
        {
            if ($this->genresRepository->findGenreByName($GenreName)) //if ($entityManager->find(Genre::class, $GenreName))
            {
                continue;
            }

            $genre = new Genre();
            $genre->setName($GenreName);

            $entityManager->persist($genre);
        }
        $entityManager->flush();

        foreach ($this->utilitiesService->getUserTypes() as $TypeName => $TypeInfo) // Auto fill DB with User Types
        {
            if ($userTypesRepository->findUserTypeById($TypeInfo["Id"]) or $userTypesRepository->findUserTypeByName($TypeName)) //($entityManager->find(UserType::class, $TypeInfo["Id"]))
            {
                continue;
            }

            $userType = new UserType();
            $userType->setName($TypeName);
            $userType->setLevel($TypeInfo["Level"]);
            $userType->setId($TypeInfo["Id"]);

            $entityManager->persist($userType);
        }

        $entityManager->flush();
    }

    #[Route('/', name: 'home', methods: ['GET', 'POST'])] // Root page
    function index(Request $request, SessionInterface $session) : Response
    {
        $pages = $this->utilitiesService->getPages();
        $pageArray = array();
        $pageArray["CurrentPage"] = "Home";

        if ($session->get("User") !== null) // If user logged in
        {
            return new Response($this->render("home.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray)));
        }
        else
        {
            return $this->redirectToRoute("login");
        }
    }

}
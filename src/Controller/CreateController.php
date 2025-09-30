<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookFormType;
use App\Repository\GenresRepository;
use App\Service\UtilitiesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CreateController extends AbstractController
{
    private $utilitiesService;

    function __construct(UtilitiesService $utilitiesService, EntityManagerInterface $entityManager)
    {
        $this->utilitiesService = $utilitiesService; //new UtilitiesService();
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])] // Create a book
    function index(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, GenresRepository $genresRepository) : Response
    {
        $user = $session->get("User");

        if ($user == null) // If user not logged in
        {
            return $this->redirectToRoute('login');
        }

        $authorisedUserTypes = $this->utilitiesService->getPageAuthorisedUserTypes($entityManager, "Create", true);

        if (!in_array($user->getUserType()->getName(), $authorisedUserTypes)) // If user not authorised through user type
        {
            return $this->redirectToRoute('home');
        }

        $pages = $this->utilitiesService->getPages();

        $pageArray = array();
        $pageArray["CurrentPage"] = "Create";
        $pageArray["Genres"] = $this->utilitiesService->getGenres(true); // Get all genres in array format

        $alert = array();


        if (isset($_POST["CreateBook"])) // If user sent a create book request
        {
            //dd($_FILES);

            $toCheck = ["BookAuthor", "BookTitle", "BookGenre"];

            foreach($toCheck as $key) // Check if all keys are set
            {
                if (!isset($_POST[$key])) // If key is not set
                {
                    $alert = $this->utilitiesService->createAlert("danger", "Please fill in the field: " . $key);
                    return new Response($this->render("create.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray, $alert)));
                }
            }

            //try
            //{
                $book = new Book();
                $genre = $genresRepository->findGenreByName($_POST["BookGenre"]); //$entityManager->find(Genre::class, $_POST["BookGenre"]);

                $book->setAuthor($_POST["BookAuthor"]);
                $book->setTitle($_POST["BookTitle"]);

                if (isset($_POST["BookDescription"])) // If book description is set
                {
                    $book->setDescription($_POST["BookDescription"]);
                }

                $book->setGenre($genre);

                if (isset($_FILES["BookImageFile"])) // If book image file submitted
                {
                    $temp = explode(".", $_FILES["BookImageFile"]["name"]);
                    $newfilename = $_POST["BookTitle"] . '.' . end($temp);
                    move_uploaded_file($_FILES["BookImageFile"]["tmp_name"], "public/Uploads/BookCovers/" . $newfilename);

                    $book->setImageURL("public/Uploads/BookCovers/" . $_POST["BookTitle"] . "." . end($temp));
                }
                else
                {
                    $book->setImageURL("public/Uploads/BookCovers/BookCoverDefaultTemplate.png");
                }

                $entityManager->persist($book);
                $entityManager->flush(); // Submit new book to DB
                $alert = $this->utilitiesService->createAlert("success", "Successfully created book!");

            //}
            //catch (\Exception $exception) // If error happens
            //{
                //$alert = $this->utilitiesService->createAlert("danger", "Unable to create a book!");
                //return new Response($this->render("create.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray, $alert)));
            //}

        }


        return new Response($this->render("create.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray, $alert)));
    }

}
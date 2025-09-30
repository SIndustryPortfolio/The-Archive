<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Review;
use App\Repository\BooksRepository;
use App\Repository\GenresRepository;
use App\Repository\ReviewsRepository;
use App\Service\DiscordService;
use App\Service\GoogleBooksService;
use App\Service\GoogleGeminiService;
use App\Service\UtilitiesService;
use ContainerE9GWfcd\getBooksControllergetBooksService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class BooksController extends AbstractController
{
    private UtilitiesService $utilitiesService;
    private GoogleBooksService  $googleBooksService;
    private GoogleGeminiService $googleGeminiService;
    private GenresRepository $genresRepository;

    private DiscordService $discordService;

    private BooksRepository $booksRepository;

    function __construct(GoogleBooksService $googleBooksService, DiscordService $discordService, GoogleGeminiService $googleGeminiService, GenresRepository $genresRepository, UtilitiesService $utilitiesService, EntityManagerInterface $entityManager, BooksRepository $booksRepository, ReviewsRepository $reviewsRepository)
    {
        $this->utilitiesService = $utilitiesService; //new UtilitiesService();
        $this->googleBooksService = $googleBooksService;
        $this->discordService = $discordService;
        $this->googleGeminiService = $googleGeminiService;
        $this->genresRepository = $genresRepository;
        $this->booksRepository = $booksRepository;
        $this->reviewsRepository = $reviewsRepository;
    }

    function getGenres(EntityManagerInterface $entityManager)
    {
        return $this->utilitiesService->getGenres();
    }

    function getBooks(EntityManagerInterface $entityManager, $allBooks, $pageNumber)
    {
        if (sizeof($allBooks) <= $this->utilitiesService->getMaxItemsPerPage())
        {
            return $allBooks;
        }

        $compactBooks = array();

        $startIndex = ($pageNumber - 1) * $this->utilitiesService->getMaxItemsPerPage();
        $endIndex = $startIndex + ($this->utilitiesService->getMaxItemsPerPage() - 1);

        if ($endIndex > count($allBooks) - 1)
        {
            $endIndex = count($allBooks) - 1;
        }

        for ($x = $startIndex; $x <= $endIndex; $x++) {
            $compactBooks[] = $allBooks[$x];
        }

        //dd($compactReviews);

        return $compactBooks;
    }

    #[Route('/deleteBook', name: 'deletebook', methods: ['POST'])] // Delete book
    public function index2(Request $request, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $user = $session->get("User");
        $data = array_merge($request->query->all(), $request->request->all()); //json_decode($request->getContent(), true);

        if ($user == null) // If user not logged in
        {
            return $this->redirectToRoute('login');
        }

        $authorisedUserTypes = $this->utilitiesService->getPageAuthorisedUserTypes($entityManager, "Create", true);

        if (!in_array($user->getUserType()->getName(), $authorisedUserTypes)) // If user not authorised through user type
        {
            return $this->redirectToRoute('home');
        }

        $foundBook = $this->booksRepository->findBookById($data["BookId"]);

        if ($foundBook)
        {
            $reviews = $this->reviewsRepository->findReviewsByBook($foundBook);

            foreach($reviews as $review)
            {
                $entityManager->remove($review);
            }

            $entityManager->remove($foundBook);
            $entityManager->flush();
        }

        //$foundBook = $entityManager->find(Book::class, $_POST["BookTitle"]);

        /**if ($foundBook)
        {
            $result = null;

            try
            {
                $qb = $entityManager->createQueryBuilder();
                $qb->select('r')
                    ->from(Review::class, 'r')
                    ->andWhere("r.book = :book")
                    ->setParameter("book", $_POST["BookTitle"]);
                $result = $qb->getQuery()->getResult(); // Get all reviews for book to erase all foreign key associations
            }
            catch(\Exception $e)
            {

            }

            foreach ($result as $bookReview)
            {
                $entityManager->remove($bookReview); // Delete book review
            }

            $entityManager->flush(); // Update DB

            $entityManager->remove($foundBook);
            $entityManager->flush(); // Update DB
        }**/

        return $this->redirectToRoute('books'); // return user back to books page
    }

    #[Route('/books', name: 'books', methods: ['GET', 'POST'])] // Books page
    function index(Request $request, SessionInterface $session, EntityManagerInterface $entityManager) : Response
    {
        if ($session->get("User") === null) // If user not logged in
        {
            return $this->redirectToRoute("login");
        }

        if ($session->get("GenreFilter") === null)
        {
            $session->set("GenreFilter", array());
        }

        // CORE
        $data = array_merge($request->query->all(), $request->request->all()); //json_decode($request->getContent(), true);


        $pageArray = array();
        $pageNumber = 1;
        $ExternalSearch = "false";

        if (isset($data["PageNumber"])) {
            $pageNumber = $data["PageNumber"];
        }

        $pageArray["PageNumber"] = $pageNumber;
        $allBooks = null;
        $newSearch = false;

        $bookQb = $entityManager->createQueryBuilder();
        $bookQb->select('r')
            ->from(Book::class, 'r'); // Get all the books


        if (isset($data["RandomSearch"]))
        {
            $data["SearchTitle"] = $this->googleGeminiService->GetRandomBookTitle() ?? "";
        }

        $searchTitle = null;

        if (isset($data["SearchTitle"]))
        {
            $searchTitle = $data["SearchTitle"];

            if ( ($session->get("SearchTitle") ?? "") != $searchTitle)
            {
                $newSearch = true;
            }

            $session->set("SearchTitle", $searchTitle);
        }

        if ($searchTitle !== null)
        {
            $pageArray["SearchTitle"] = $searchTitle;

            try
            {
                $bookQb->where("r.title like :title")
                    ->setParameter("title", "%" .  $pageArray["SearchTitle"] . "%"); // Add search title condition (non-case sensitive)
            }
            catch(\Exception $e)
            {

            }
        }
        else
        {

        }

        if (isset($_POST["removeGenre"])) // If user submits a remove request for the Genre filter
        {
            $genreArray = $session->get("GenreFilter");
            $index = array_search($_POST["removeGenre"], $genreArray);


            if ($index !== false)
            {
                unset($genreArray[$index]);
                $session->set("GenreFilter", $genreArray);
            }
        }

        if (isset($_GET["Genre"])) // If user submits a genre request to add to the filtering
        {
            $genreArray = $session->get("GenreFilter");

            if (!in_array($_GET["Genre"], $genreArray)) //(array_search($_GET["Genre"], $genreArray) === false)
            {
                $genreArray[] = $_GET["Genre"];

                $session->set("GenreFilter", $genreArray);
            }
        }

        $genreCount = 0;
        $genreFilterArray = $session->get("GenreFilter");
        foreach ($genreFilterArray as $genre) // Handle genre filtering to books
        {
            $genreObject = $this->genresRepository->findGenreByName($genre);

            if ($genre)
            {
                $bookQb->orHaving("r.genre = :genre" . $genreCount)
                    ->setParameter("genre" . $genreCount, $genreObject);
            }

            $genreCount++;
        }

        $alert = array();

        try
        {
            $allBooks = $bookQb->getQuery()->getResult();
        }
        catch(\Exception $e)
        {
            $alert = $this->utilitiesService->createAlert("danger", "Failed to load! :(");
        }

        $allBooks = $allBooks ?? [];

        $maxPageNumber = 0;
        $numberOfBooks = count($allBooks);

        if (isset($data["ExternalSearch"]))
        {
            $translate = array(
                "true" => "true",
                "false" => "false",
                true => "true",
                false => "false"
            );

            //dd($data["ExternalSearch"]);

            $ExternalSearch = $translate[$data["ExternalSearch"] ?? false];
            $pageArray["ExternalSearch"] = $ExternalSearch;

            if ($ExternalSearch == "true") {
                $googleBooks = $this->googleBooksService->searchBooks($searchTitle ?? "", $pageNumber, $genreFilterArray);

                $numberOfBooks += $googleBooks["totalItems"] ?? 0;

                //$maxPageNumber += ($googleBooks["totalItems"] ?? 0) / 10;

                foreach (($googleBooks["items"] ?? []) as $googleBook)
                {
                    $book = $this->googleBooksService->getBookFromItem($googleBook);

                    $allBooks[] = $book;

                }

                //$pageArray["GoogleBooks"] = $googleBooks;
            }
        }

        $maxPageNumber = $this->utilitiesService->clamp(ceil($numberOfBooks / $this->utilitiesService->getMaxItemsPerPage()), 0, 20);
        $allBooksInPage = $this->getBooks($entityManager, $allBooks, $pageNumber); // Shortlist results to maximum number of 10 books

        $_books = array();

        foreach ($allBooksInPage as $book) // Reformat all book objects as accessible arrays
        {
            $_books[] = array("Id" => $book->getId(), "Title" => $book->getTitle(), "IsExternal" => $book->getIsExternal(), "Author" => $book->getAuthor(), "Genre" => $book->getGenre()->getName(), "ImageURL" => $book->getImageURL(), "Description" => $book->getDescription());
        }


        if (sizeof($_books) <= 0) // If there are no books after all filtering
        {
         $alert = $this->utilitiesService->createAlert("danger", "No results found!");
        }

        $pageArray["Books"] = $_books; // All books objects as arrays
        $pageArray["MaxPageNumber"] = $maxPageNumber; // The last page of books there can be
        $pageArray["NumberOfBooks"] = $numberOfBooks; // Total number of books
        $pageArray["CurrentPage"] = "Books"; // Current name of page
        $pageArray["Genres"] = $this->getGenres($entityManager); // All Genres in array format
        $pageArray["SelectedGenres"] = $session->get("GenreFilter"); // Selected Genres in array format
        $pageArray["DQL"] = $bookQb->getQuery()->getDQL(); // String query for debug purpose -> No longer used

        //dd($pageArray["Books"]);

        if ($newSearch)
        {
            $genresString = "";

            if (!empty($genreFilterArray)) {
                foreach($genreFilterArray as $genre)
                {
                    $genresString .= "- " . $genre . "\n";
                }
            }
            else
            {
                $genresString = "NO GENRE FILTER";
            }

            $embed = [
                "title" => "Search: '" . $searchTitle . "'",
                "description" => "",
                "color" => hexdec("00FF00"),
                "fields" => [
                    [
                        "name" => "User",
                        "value" => "Username: " . $session->get("User")->getUsername() . "\n User Type: " . $session->get("User")->getUserType()->getName()
                    ],
                    [
                      "name" => "Genres",
                      "value" => $genresString
                    ],
                    [
                        "name" => "Meta",
                        "value" => "Max page number: " . $maxPageNumber . "\n Number of books: " . $numberOfBooks . "\n External Search: " . $ExternalSearch,
                    ]
                ],
                "timestamp" => date("c")
            ];

            $this->discordService->sendMessage("SearchLogs", $embed);
        }

        return new Response($this->render("books.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray, $alert)));
    }

}
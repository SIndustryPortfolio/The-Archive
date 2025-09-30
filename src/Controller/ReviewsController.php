<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\BooksRepository;
use App\Repository\ReviewsRepository;
use App\Repository\UsersRepository;
use App\Service\UtilitiesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class ReviewsController extends AbstractController
{
    private $utilitiesService;
    private $booksRepository;
    private $reviewsRepository;
    private $usersRepository;

    function __construct(EntityManagerInterface $entityManager, BooksRepository $booksRepository, ReviewsRepository $reviewsRepository, UsersRepository $usersRepository, UtilitiesService $utilitiesService)
    {
        $this->utilitiesService = $utilitiesService; //new UtilitiesService();
        $this->booksRepository = $booksRepository;
        $this->reviewsRepository = $reviewsRepository;
        $this->usersRepository = $usersRepository;

    }

    function getReviews(EntityManagerInterface $entityManager, $AllReviews, $pageNumber) // Filter reviews through page number (return 10 results)
    {
        //$AllReviews = $book->getReviews();
        $compactReviews = array();

        $startIndex = ($pageNumber - 1) * $this->utilitiesService->getMaxItemsPerPage();
        $endIndex = $startIndex + ($this->utilitiesService->getMaxItemsPerPage() - 1);

        if ($endIndex > count($AllReviews) - 1)
        {
            $endIndex = count($AllReviews) - 1;
        }

        for ($x = $startIndex; $x <= $endIndex; $x++) {
            $compactReviews[] = $AllReviews[$x];
        }

        //dd($compactReviews);

        return $compactReviews;
    }

    #[Route('/editReview', name: 'editReview', methods: ['POST'])] // Edit review
    function index4(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, ReviewsRepository $reviewsRepository) : Response
    {
        $user = $session->get("User");
        $data = array_merge($request->query->all(), $request->request->all()); //json_decode($request->getContent(), true);

        if ($user === null) // If User is not logged in
        {
            return $this->redirectToRoute('login');
        }

        $pageArray = array();
        $result = null;

        $pageArray["BookTitle"] = $data["ReviewBookTitle"] ?? null;
        $pageArray["BookId"] = $data["ReviewBookId"] ?? null;

        $book = null;

        if (isset($data["ReviewBookTitle"]))
        {
            $book = $this->booksRepository->findBookById($data["ReviewBookId"]);
        }

        if (isset($data["ReviewBookId"]))
        {
            $book = $this->booksRepository->findBookById($data["ReviewBookId"]);
        }

        try
        {
            $result = $reviewsRepository->findReview($book, $user);
        }
        catch(\Exception $e)
        {

        }

        if (!$result) // If failed to get review to edit
        {
            return $this->redirectToRoute("reviews", $pageArray); // return to the reviews
        }

        if (isset($data["ReviewRating"]))
        {
            $result->setRating($_POST["ReviewRating"]); // Change rating (stars)
        }

        if (isset($data["ReviewContent"]))
        {
            $result->setContent($_POST["ReviewContent"]); // Change content (review description)
        }

        $entityManager->persist($result);
        $entityManager->flush(); // Push results to DB

        return $this->redirectToRoute("reviews", $pageArray); // Redirect back to reviews page
    }

    #[Route('/deleteReview', name: 'deleteReview', methods: ['POST'])]
    function index3(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, ReviewsRepository $reviewsRepository) : Response
    {
        $user = $session->get("User");
        $data = array_merge($request->query->all(), $request->request->all()); //json_decode($request->getContent(), true);

        if ($user === null)
        {
            return $this->redirectToRoute('login');
        }

        $pageArray = array();
        $result = null;

        $pageArray["BookTitle"] = $data["ReviewBookTitle"] ?? null;
        $pageArray["BookId"] = $data["ReviewBookId"] ?? null;

        $book = null;

        if (isset($data["ReviewBookTitle"]))
        {
            $book = $this->booksRepository->findBookByTitle($data["ReviewBookTitle"]);
        }

        if (isset($data["ReviewBookId"]))
        {
            $book = $this->booksRepository->findBookById($data["ReviewBookId"]);
        }

        try
        {
            $result = $reviewsRepository->findReview($book, $user); //$reviewsRepository->findReviewsByBook($book);
        }
        catch(\Exception $e)
        {

        }

        if ($result)
        {
            $entityManager->persist($result);
            $entityManager->remove($result);
            $entityManager->flush(); // Remove User book review from database
        }

        return $this->redirectToRoute("reviews", $pageArray); // Redirect back to reviews page
    }

    #[Route('/addReview', name: 'addReview', methods: ['POST'])] // Add Review
    function index2(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, UsersRepository $usersRepository, ReviewsRepository $reviewsRepository, BooksRepository $booksRepository) : Response
    {
        $user = $session->get("User");
        $data = array_merge($request->query->all(), $request->request->all()); //json_decode($request->getContent(), true);

        if ($user === null) // If user not logged in
        {
            return $this->redirectToRoute("login");
        }

        $pageArray = array();
        $pageArray["BookTitle"] = $data["ReviewBookTitle"] ?? null;
        $pageArray["BookId"] = $data["ReviewBookId"] ?? null;

        $toCheckIsSet = ["ReviewRating"];

        foreach ($toCheckIsSet as $key) // Check if all fields are set in form
        {
            if (!isset($data[$key])) // If not field is set
            {
                return $this->redirectToRoute("reviews"); // Redirect back to reviews page
            }
        }

        $book = null;

        if (isset($data["ReviewBookTitle"]))
        {
            $book = $this->booksRepository->findBookByTitle($data["ReviewBookTitle"]);
        }

        if (isset($data["ReviewBookId"]))
        {
            $book = $this->booksRepository->findBookById($data["ReviewBookId"]);
        }


        $result = null;

        try
        {
            $result = $reviewsRepository->findReview($book, $user->getUsername());
        }
        catch(\Exception $e)
        {

        }


        if ($result) // If review already exists from user
        {
            return $this->redirectToRoute("reviews", $pageArray); // Redirect back to reviews page
        }

        $book = null;

        if (isset($data["ReviewBookTitle"]))
        {
            $book = $booksRepository->findBookByTitle($data["ReviewBookTitle"]);
        }

        if (isset($data["ReviewBookId"]))
        {
          $book = $booksRepository->findBookById($data["ReviewBookId"]);
        }

        $review = new Review();
        $review->setUser($usersRepository->findUserByUserId($user->getId()));   //$entityManager->find(User::class, $user->getUsername()));
        $review->setBook($book);
        $review->setRating($data["ReviewRating"]);
        $review->setContent($data["ReviewContent"]);

        $entityManager->persist($review);
        $entityManager->flush(); // Add review to the DB

        return $this->redirectToRoute("reviews", $pageArray); // Redirect back to reviews page
    }


    #[Route('/reviews', name: 'reviews', methods: ['GET'])]
    function index(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, UsersRepository $usersRepository, BooksRepository $booksRepository, ReviewsRepository $reviewsRepository) : Response
    {
        $pages = $this->utilitiesService->getPages();
        $user = $session->get("User");
        $data = array_merge($request->query->all(), $request->request->all()); //json_decode($request->getContent(), true);


        if ($user === null)
        {
            return $this->redirectToRoute("login");
        }

        $pageNumber = 1;

        if (isset($data["PageNumber"])) {
            $pageNumber = $data["PageNumber"];
        }

        if (!isset($data["BookTitle"]) and !isset($data["BookId"]))
        {
            return $this->redirectToRoute("books");
        }

        $book = null;

        if (isset($data["BookTitle"]))
        {
            $book = $booksRepository->findBookByTitle($data["BookTitle"]); //$entityManager->find(Book::class, $_GET["BookTitle"]);
        }

        if (isset($data["BookId"]))
        {
            $book = $booksRepository->findBookById($data["BookId"]);
        }

        $allReviews = $book->getReviews();
        $maxPageNumber = ceil(count($allReviews) / $this->utilitiesService->getMaxItemsPerPage());
        $numberOfReviews = count($allReviews);

        $reviews = $this->getReviews($entityManager, $allReviews, $pageNumber); //$book->getReviews();
        $_book =  array("Title" => $book->getTitle(), "Id" => $book->getId(), "Author" => $book->getAuthor(), "ImageURL" => $book->getImageURL());

        $result = $reviewsRepository->getAverageRating($book);

        $userReview = $reviewsRepository->findReview($book, $user);

        $_book["AverageRating"] = (int)$result;
        $_userReview = null;

        if ($userReview)
        {
            $_userReview =  array(
                "iD" => $userReview->getId(),
                "rating" => $userReview->getRating(),
                "username" => $userReview->getUser()->getUsername(),
                "content" => $userReview->getContent()
            );
        }

        $_reviews = array();

        foreach ($reviews as $review)
        {
            $_reviews[] = array(
                "iD" => $review->getId(),
                "rating" => $review->getRating(),
                "username" => $review->getUser()->getUsername(),
                "content" => $review->getContent()
            );
        }

        $pageArray = array("CurrentPage" => "Reviews", "Book" => $_book, "Reviews" => $_reviews, "PageNumber" => $pageNumber, "MaxPageNumber" => $maxPageNumber, "NumberOfReviews" => $numberOfReviews);

        if ($_userReview !== null)
        {
            $pageArray["UserReview"] = $_userReview;
        }

        return new Response($this->render("reviews.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray)));
    }

}
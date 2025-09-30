<?php

namespace App\Controller\Api\V1;

use App\Entity\Book;
use App\Repository\BooksRepository;
use App\Repository\GenresRepository;
use App\Repository\ReviewsRepository;
use App\Repository\UsersRepository;
use App\Service\SecureService;
use Doctrine\ORM\EntityManagerInterface;
use Google\Cloud\ApigeeConnect\V1\HttpResponse;
use Google\Cloud\BigQuery\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/api/v1/books")]
class BooksAPIController extends AbstractController
{
    private $entityManager;
    private $reviewsRepository;
    private $usersRepository;
    private $booksRepository;

    private $genresRepository;

    private $secureService;

    public function __construct(EntityManagerInterface $entityManager, SecureService $secureService, ReviewsRepository $reviewsRepository, UsersRepository $usersRepository, BooksRepository $booksRepository, GenresRepository $genresRepository)
    {
        // Functions
        // INIT
        $this->entityManager = $entityManager;
        $this->reviewsRepository = $reviewsRepository;
        $this->usersRepository = $usersRepository;
        $this->booksRepository = $booksRepository;
        $this->genresRepository = $genresRepository;
        //
        $this->secureService = $secureService;
    }

    #[Route("", methods: ["GET"])] // GET BOOKS -> MULTI | Optional: "genreId"
    public function listBooks(Request $request) : JsonResponse
    {
        // Functions
        // INIT
        $this->secureService->secureDenyAccessUnlessGranted($request, "Guest");

        try
        {
            $data = array_merge($request->query->all(), $request->request->all());

            $books = null;

            if (isset($data["genreId"]))
            {
                $genre = $this->genresRepository->findGenreById($data["genreId"]);

                if (!$genre)
                {
                    return new JsonResponse(["message" => "Invalid genre ID!"], Response::HTTP_BAD_REQUEST);
                }

                $books = $this->booksRepository->findBooksByGenre($genre);
            }
            else
            {
                $books = $this->booksRepository->findAllBooks();
            }

            $returnArray = [];

            foreach ($books as $book)
            {
                $returnArray[] = $book->getDict();
            }

            return new JsonResponse($returnArray, Response::HTTP_OK); //$this->json($returnArray, Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to get books!"], Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }

    #[Route("", methods: ["POST"])] // CREATE BOOK | Needed: "genreId", "title", "description", "author" | Optional: "imageURL"
    public function createBook(Request $request) : JsonResponse
    {
        // Functions
        // INIT
        $this->secureService->secureDenyAccessUnlessGranted($request, "Author");

        try
        {
            $data = array_merge($request->query->all(), $request->request->all());

            $genre = $this->genresRepository->findGenreById($data["genreId"]);


            $requiredParams = ["title", "author", "description", "genreId"];

            foreach ($requiredParams as $param)
            {
                if (!isset($data[$param]))
                {
                    return new JsonResponse(["message" => $param . " not found."], Response::HTTP_BAD_REQUEST);
                }
            }

            if (!$genre)
            {
                return new JsonResponse(["message" => "Invalid genre id."], Response::HTTP_BAD_REQUEST);
            }

            try
            {
                $book = new Book();

                $book->setTitle($data['title']);

                $book->setDescription($data['description']);

                $book->setImageURL($data['imageURL'] ?? "public/Uploads/BookCovers/BookCoverDefaultTemplate.png");

                $book->setAuthor($data['author']);

                $book->setGenre($genre);

                $this->entityManager->persist($book);
                $this->entityManager->flush();
            }
            catch (\Exception $e)
            {
                return new JsonResponse(["message" => "Failed to create book!"], Response::HTTP_BAD_REQUEST);
            }

            return new JsonResponse(["message" => "Successfully created book!"], Response::HTTP_CREATED);
        }
        catch (\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to create book!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route("/{iD}/reviews", methods: ["GET"])] // GET BOOK REVIEWS | Needed: "iD"
    public function listReviews(Request $request, int $iD) : JsonResponse
    {
        $this->secureService->secureDenyAccessUnlessGranted($request, "Guest");

        try {
            // Functions
            // INIT
            $book = $this->booksRepository->findBookById($iD);

            if (!$book) {
                return new JsonResponse(["message" => "Book not found!"], Response::HTTP_NOT_FOUND);
            }

            $reviews = $this->reviewsRepository->findReviewsByBook($book);

            if (!$reviews) {
                return new JsonResponse(["message" => "No reviews found!"], Response::HTTP_NOT_FOUND);
            }

            $responseArray = [];

            foreach ($reviews as $review) {
                $responseArray[] = $review->getDict();
            }

            return new JsonResponse($responseArray, Response::HTTP_OK); //$this->json($book->getReviews(), Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to get reviews from book!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    #[Route("/{iD}", methods: ["DELETE"])] // DELETE BOOK | Needed: "iD"
    public function deleteBook(Request $request, int $iD)
    {
        // Functions
        // INIT
        $this->secureService->secureDenyAccessUnlessGranted($request, "Admin");

        try
        {
            $book = $this->booksRepository->findBookById($iD);

            if (!$book)
            {
                return new JsonResponse(["message" => "Book not found!"], Response::HTTP_NOT_FOUND);
            }

            $reviews = $this->reviewsRepository->findReviewsByBook($book);

            foreach ($reviews as $review)
            {
                $this->entityManager->remove($review);
            }

            $this->entityManager->remove($book);
            $this->entityManager->flush();

            return new JsonResponse(["message" => "Successfully deleted book!"], Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to delete book!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route("/{iD}", methods: ["PUT"])] // EDIT BOOK | NEEDED: "iD" | OPTIONAL: "title", "description", "author", "genreId", "imageURL"
    public function editBook(Request $request, int $iD) : JsonResponse
    {
        // Functions
        // INIT
        $this->secureService->secureDenyAccessUnlessGranted($request, "Admin");

        try
        {
            $book = $this->booksRepository->findBookById($iD);
            //$data = json_decode($request->getContent(), true);
            $data = array_merge($request->query->all(), $request->request->all());

            if (!$book) {
                return new JsonResponse(["message" => "Book not found!"], Response::HTTP_NOT_FOUND);
            }

            if (isset($data["title"])) {
                $book->setTitle($data["title"]);
            }

            if (isset($data["description"])) {
                $book->setDescription($data["description"]);
            }

            if (isset($data["author"])) {
                $book->setAuthor($data["author"]);
            }

            if (isset($data["genreId"])) {
                $genre = $this->genresRepository->findGenreById($data["genreId"]);

                if ($genre) {
                    $book->setGenre($genre);
                } else {
                    return new JsonResponse(["message" => "Invalid genre Id"], Response::HTTP_BAD_REQUEST);
                }
            }

            if (isset($data["imageURL"])) {
                $book->setImageURL($data["imageURL"]);
            }

            $this->entityManager->flush();

            return new JsonResponse(["message" => "Successfully editted book!"], Response::HTTP_OK); //$this->json($book, Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to edit book!", Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }

    #[Route("/{iD}", methods: ["GET"])] // GET BOOK | Needed: "iD"
    public function getBook(Request $request, int $iD)
    {
        // Functions
        // INIT
        $this->secureService->secureDenyAccessUnlessGranted($request, "Reader");

        try
        {
            $book = $this->booksRepository->findBookById($iD);

            if (!$book)
            {
                return new JsonResponse(["message" => "Book not found!"], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse($book->getDict(), Response::HTTP_OK); //$this->json($book, Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to get book!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
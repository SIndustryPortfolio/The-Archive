<?php

namespace App\Controller\Api\V1;

use App\Entity\Review;
use App\Repository\BooksRepository;
use App\Repository\ReviewsRepository;
use App\Repository\UsersRepository;
use App\Service\SecureService;
use Doctrine\ORM\EntityManagerInterface;
use PhpCsFixer\Console\Report\FixReport\JsonReporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;

#[Route("/api/v1/reviews")]
class ReviewsAPIController extends AbstractController
{
    private $entityManager;
    private $reviewsRepository;
    private $usersRepository;
    private $booksRepository;

    private $secureService;

    public function __construct(EntityManagerInterface $entityManager, ReviewsRepository $reviewsRepository, UsersRepository $usersRepository, BooksRepository $booksRepository, SecureService $secureService)
    {
        // Functions
        // INIT
        $this->entityManager = $entityManager;
        $this->reviewsRepository = $reviewsRepository;
        $this->usersRepository = $usersRepository;
        $this->booksRepository = $booksRepository;
        $this->secureService = $secureService;
    }

    #[Route("", methods: ["POST"])] // CREATE REVIEW | Needed: "rating", "content", "userId", "bookId"
    public function createReview(Request $request) : JsonResponse
    {

        // Functions
        // INIT
        $this->secureService->secureDenyAccessUnlessGranted($request, "Admin");

        try {
            $data = array_merge($request->query->all(), $request->request->all());

            if (!isset($data["rating"])) {
                return new JsonResponse(["message" => "Rating is required!"], Response::HTTP_BAD_REQUEST);
            } elseif ((int)$data["rating"] < 1 or (int)$data["rating"] > 5) {
                return new JsonResponse(["message" => "Rating is Invalid! Keep rating between 1 - 5."], Response::HTTP_BAD_REQUEST);
            }

            try {
                $user = $this->usersRepository->findUserByUserId($data['userId']);

                if (!$user) {
                    return new JsonResponse(["message" => "User not found"], Response::HTTP_NOT_FOUND);
                }

                $book = $this->booksRepository->findBookById($data['bookId']);

                if (!$book) {
                    return new JsonResponse(["message" => "Book not found"], Response::HTTP_NOT_FOUND);
                }

                $existingReview = $this->reviewsRepository->findReview($book, $user);

                if ($existingReview)
                {
                    return new JsonResponse(["message" => "Review already exists!"], Response::HTTP_BAD_REQUEST);
                }

                $review = new Review();
                $review->setUser($user);
                $review->setBook($book);
                $review->setRating($data['rating']);

                if (isset($data["content"])) {
                    $review->setContent($data['content']);
                }

                $this->entityManager->persist($review);
                $this->entityManager->flush();

                return new JsonResponse(["message" => "Successfully created review!"], Response::HTTP_CREATED);
            } catch (\Exception $e) {
                return new JsonResponse(["message" => "Failed to create review!"], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        catch (\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to create review!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route("/{iD}", methods: ["DELETE"])] // DELETE REVIEW (Sub domain) | Needed: "ID"
    public function deleteReview(Request $request, int $iD) : JsonResponse
    {

        // Functions
        // INIT
        $this->secureService->secureDenyAccessUnlessGranted($request, "Admin");

        try
        {
            $review = $this->reviewsRepository->findReviewById($iD);


            if (!$review)
            {
                return new JsonResponse(["message" => "Review not found!"], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($review);
            $this->entityManager->flush();

            return new JsonResponse(["message" => "Review deleted successfully"], Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to delete review!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route("/{iD}", methods: ["PUT"])] // EDIT REVIEW (Sub domain) | Needed: "iD", "content", "rating"
    public function editReview(Request $request, int $iD) : JsonResponse
    {
        // Functions
        // INIT
        $this->secureService->secureDenyAccessUnlessGranted($request, "Admin");

        try {
            $review = $this->reviewsRepository->findReviewById($iD);

            if (!$review) {
                return new JsonResponse(["message" => "Review not found!"], Response::HTTP_NOT_FOUND);
            }

            $data = array_merge($request->query->all(), $request->request->all());

            if (isset($data["content"])) {
                $review->setContent($data['content']);
            }

            if (isset($data["rating"])) {
                if ((int)$data["rating"] < 1 or (int)$data["rating"] > 5) {
                    return new JsonResponse(["message" => "Rating is Invalid! Keep rating between 1 - 5."], Response::HTTP_BAD_REQUEST);
                }

                $review->setRating($data['rating']);
            }

            $this->entityManager->flush();
            return new JsonResponse(["message" => "Successfully editted review!"]); //$this->json($review, Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to edit review!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route("/{iD}", methods: ['GET'])] // GET REVIEW (Sub domain) | Needed: "iD"
    public function getReview(Request $request, int $iD) : JsonResponse
    {

        // Functions
        // INIT
        $this->secureService->secureDenyAccessUnlessGranted($request, "Guest");

        try
        {
            $review = $this->reviewsRepository->findReviewById($iD);

            if (!$review)
            {
                return new JsonResponse(["message" => "Review not found!"], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse($review->getDict(), Response::HTTP_OK); //$this->json($review, Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            return new JsonResponse(["message" => "Failed to get review!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
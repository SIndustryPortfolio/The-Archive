<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Genre;
use App\Entity\User;
use App\Repository\UsersRepository;
use App\Repository\UserTypesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
Use Symfony\Contracts\Cache\CacheInterface;
Use Symfony\Contracts\Cache\ItemInterface;


class SecureService extends AbstractController
{
    private $usersRepository;
    private $entityManager;

    private $cache;

    private $rateLimitThreshold;

    private $rateLimitTimeout;
    private $maxRequestQuantityInThreshold;



    public function __construct(UsersRepository $usersRepository, EntityManagerInterface $entityManager, UserTypesRepository $userTypesRepository, UtilitiesService $utilitiesService, CacheInterface $cache)
    {
        $this->usersRepository = $usersRepository;
        $this->entityManager = $entityManager;
        $this->userTypesRepository = $userTypesRepository;
        $this->utilitiesService = $utilitiesService;
        $this->cache = $cache;

        $this->rateLimitThreshold = 10; // Seconds
        $this->maxRequestQuantityInThreshold = 100; // Number of requests

        $this->rateLimitTimeout = 30; // Seconds
    }

    public function rateLimit($token)
    {
        $response = false;
        $tick = $this->utilitiesService->getTick();
        $defaultTable = [
            "Requests" => 0,
            "StartTick" => $tick
        ];

        $item = $this->cache->getItem("rate-limit-" . $token);
        $rateLimitTable = null;

        if (!$item->isHit())
        {
            $rateLimitTable = $defaultTable;
        }
        else
        {
            $rateLimitTable = $item->get() ?? $defaultTable;
        }

        $rateLimitTable["Requests"]++;

        if ($rateLimitTable["Limited"] ?? false)
        {
            if ($tick - $rateLimitTable["LastTick"] > $this->rateLimitTimeout)
            {
                $this->cache->deleteItem("rate-limit-" . $token);
                return $this->rateLimit($token);
            }
            else
            {
                $response = true;
            }
        }
        else
        {
            if ($rateLimitTable["Requests"] > $this->maxRequestQuantityInThreshold)
            {
                $rateLimitTable["Limited"] = true;
                $response = true;
            }

            $rateLimitTable["LastTick"] = $tick;
        }

        $item->set($rateLimitTable);
        //$item->expiresAfter($this->rateLimitTimeout * 2); // If not manually expired
        $this->cache->save($item);

        return $response;
    }

    public function secureDenyAccessUnlessGranted(Request $request, $userTypeName)
    {
        // Functions
        // INIT
        $userToken = $request->headers->get('authorisation', null);

        if (!$userToken or empty($userToken))
        {
            throw new AccessDeniedHttpException("No token provided!");
        }

        $rateLimitResponse = $this->rateLimit($userToken);

        if ($rateLimitResponse)
        {
            throw new TooManyRequestsHttpException(30, 'Rate limit exceeded. Try again in 30 seconds!');
        }

        $userType = $this->userTypesRepository->findUserTypeByName($userTypeName);

        if (!$userType) {
            throw new AccessDeniedHttpException("Fatal error!");
        }

        $userTypeLevel = $userType->getLevel();

        if ($userTypeLevel <= 0)
        {
            return;
        }

        $user = $this->usersRepository->findByApiToken(['token' => $userToken]);

        if (!$user)
        {
            if ($userTypeName)
            throw new AccessDeniedHttpException("Invalid token!");
        }

        $usersUserTypeLevel = $user->getUserType()->getLevel();

        if ($usersUserTypeLevel < $userTypeLevel)
        {
            throw new AccessDeniedHttpException("Lacking permissions for command!");
        }
    }

    public function setAPITokenForUser($userId)
    {
        $user = $this->usersRepository->findUserByUserId($userId);
        $user->setApiToken(bin2hex(random_bytes($_ENV["MY_API_TOKEN_BYTES"])));

        //
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
<?php

namespace App\Controller;

use App\Entity\Credentials;
use App\Form\CredentialsFormType;
use App\Repository\UsersRepository;
use App\Service\UtilitiesService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Entity\User;


class LoginController extends AbstractController
{
    private $utilitiesService;
    private $userAuthenticator;
    private $authenticator;

    function __construct(UtilitiesService $utilitiesService)
    {
        $this->utilitiesService = $utilitiesService; //new UtilitiesService();
    }

    //
    function AuthenticateCredentials(Credentials $credentials, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UsersRepository $usersRepository) // If user can login with credentials
    {

        $foundUser = null;

        try
        {
            $foundUser = $usersRepository->findUserByUsername($credentials->getUsername());
        }
        catch (EntityNotFoundException $e)
        {
            return null;
        }

        if (!$foundUser)
        {
            return null; // Not found user
        }

        if ($passwordHasher->isPasswordValid($foundUser, $credentials->getPassword())) // Check if password matches against encrypted one
        {
            return $foundUser; // Successful login
        }

        return null; // Unsuccessful login
    }

    #[Route('/logout', name: 'logout', methods: ['POST', 'GET'])] // Logout
    function logout(Request $request, SessionInterface $session, EntityManagerInterface $entityManager) : Response
    {
        $session->clear(); // Destroy session (all current data in session cleared causing logout)
        return $this->redirectToRoute('login');
    }

    #[Route('/login', name: 'login', methods: ['GET', 'POST'])] // Login
    function index(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UsersRepository $usersRepository) : Response
    {
        $pageArray = array();
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsFormType::class, $credentials);

        $form->handleRequest($request);

        $pageArray["LoginForm"] = $form->createView();
        $pageArray["CurrentPage"] = "Login";

        if ($form->isSubmitted() && $form->isValid()) // If login form submitted
        {
            $entityManager->persist($credentials);
            // NOT FLUSHING! -> Not storing temp user input
            $user = $this->AuthenticateCredentials($credentials, $entityManager, $passwordHasher, $usersRepository); // Check if username and password are valid

            //

            if ($user === null) // If failed login
            {
                $alert = $this->utilitiesService->createAlert("danger", "Invalid login credentials!");
                return new Response($this->render("login.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray, $alert)));
            }

            //$this->loginUserManually($user, $request, $this->userAuthenticator, $this->authenticator);

            $session->set("User", $user); // Successful login, set the user in the session
            return $this->redirectToRoute("home");
        }

        if ($session->get("User") !== null) // If user is logged in
        {
            return $this->redirectToRoute("home");
        }
        else // If user not logged in
        {
            return new Response($this->render("login.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray)));
        }
    }
}
<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserType;
use App\Form\RegisterCredentialsFormType;
use App\Repository\BooksRepository;
use App\Repository\UsersRepository;
use App\Repository\UserTypesRepository;
use App\Service\UtilitiesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    private UtilitiesService $utilitiesService;
    private UserTypesRepository $userTypesRepository;

    function __construct(UtilitiesService $utilitiesService, EntityManagerInterface $entityManager, UsersRepository $usersRepository, UserTypesRepository $userTypesRepository)
    {
        $this->utilitiesService = $utilitiesService; //new UtilitiesService();
        $this->userTypesRepository = $userTypesRepository;
    }

    public function RegistrationFailed($session, $form)
    {
        $alert = $this->utilitiesService->createAlert("danger", "Unable to create user!");
        return new Response($this->render("register.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), array("RegisterForm" => $form->createView(), "CurrentPage" => "Register"), $alert)));
    }

    function handleRegistration(UsersRepository $userRepository, $entityManager, $session, $form, $passwordHasher)
    {
        $pageArray = array();

        $formData = $form->getData();
        $foundUser = $userRepository->findUserByUsername($formData->getUsername());

        $pageArray["RegisterForm"] = $form->createView();
        $pageArray["CurrentPage"] = "Register";

        if ($foundUser) // If user already exists with username
        {
            $alert = $this->utilitiesService->createAlert("danger", "User already exists!");

            return new Response($this->render("register.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray, $alert)));
        }

        $User = new User(); // Create new user
        $User->setUsername($formData->getUsername());

        $hashedPassed = $passwordHasher->hashPassword($User, $formData->getPassword());
        $User->setPassword($hashedPassed);
        $User->setProfilePictureURL("public/Uploads/ProfilePictures/DefaultProfilePicture.png");

        $UserType = $this->userTypesRepository->findUserTypeByName("Reader");

        try
        {
            $User->setUserType($UserType);
            //$User->setUserType($entityManager->find(UserType::class, 1)); // Auto set user type to 'reader'
        }
        catch (\Exception $exception)
        {
            return $this->RegistrationFailed($session, $form);
        }

        try
        {
            $entityManager->persist($User);
            $entityManager->flush(); // Update database
        }
        catch (\Exception $e)
        {
           return $this->RegistrationFailed($session, $form);
        }

        return $this->redirectToRoute("login"); // redirect
    }

    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    function index(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UsersRepository $usersRepository) : Response
    {
        if ($session->get("User") !== null) // If user logged in
        {
            return $this->redirectToRoute("home");
        }

        $pageArray = array();

        $form = $this->createForm(RegisterCredentialsFormType::class);
        $form->handleRequest($request);

        $pageArray["RegisterForm"] = $form->createView();
        $pageArray["CurrentPage"] = "Register";

        if ($form->isSubmitted() && $form->isValid()) // If register form submitted
        {
            return $this->handleRegistration($usersRepository, $entityManager, $session, $form, $passwordHasher);
        }

        return new Response($this->render("register.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray)));
    }
}
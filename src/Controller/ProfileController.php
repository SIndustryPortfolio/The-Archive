<?php

namespace App\Controller;

use App\Form\ApiTokenFormType;
use App\Form\EditProfileFormType;
use App\Repository\UsersRepository;
use App\Service\SecureService;
use App\Service\UtilitiesService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    private $utilitiesService;
    private $secureService;

    private $usersRepository;

    function __construct(UtilitiesService $utilitiesService, EntityManagerInterface $entityManager, SecureService $secureService, UsersRepository $usersRepository)
    {
        $this->utilitiesService = $utilitiesService;
        $this->secureService = $secureService;
        $this->usersRepository = $usersRepository;
    }

    function handleProfileUpdate(EntityManagerInterface $entityManager, UsersRepository $usersRepository, $passwordHasher, SessionInterface $session, $form) // Gets data from form upload and changes user credentials
    {
        $formData = $form->getData();
        $userProfile = $usersRepository->findUserByUserId($session->get("User")->getId()); //$entityManager->find(User::class, $session->get("User")->getUsername()); //$session->get("User");

        $profilePictureUpload = $form->get('ProfilePictureUpload')->getData();

        if ($profilePictureUpload) // If profile picture has been uploaded
        {
            $originalFilename = pathinfo($profilePictureUpload->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = $userProfile->getUsername() . '.' . $profilePictureUpload->guessExtension();

            if (($userProfile->getProfilePictureURL() !== "public/Uploads/Misc/DefaultProfilePicture.png") &&  file_exists($userProfile->getProfilePictureURL())) // If old profile picture exists and isn't the default profile picture for users
            {
                unlink($userProfile->getProfilePictureURL()); // Remove old profile picture
            }

            try {
                $profilePictureUpload->move('public/Uploads/ProfilePictures', $newFilename); // Move the uploaded picture to the profile pictures folder
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            $userProfile->setProfilePictureURL("public/Uploads/ProfilePictures/" . $newFilename); // Update user profile picture URL
        }

        if (isset($formData["Password"]) and isset($formData["ConfirmPassword"])) // If password requested to be changed
        {
            if ($formData["Password"] === $formData["ConfirmPassword"])
            {
                $hashedPassed = $passwordHasher->hashPassword($userProfile, $formData["Password"]); // Encrypt password string
                $userProfile->setPassword($hashedPassed); // Update password on user
            }
            else
            {
                throw new Exception('Passwords do not match'); // Throw exception so user can be alerted of an error
            }
        }

        $session->set("User", $userProfile); // Update user in session

        $entityManager->persist($userProfile);
        $entityManager->flush(); // Update database

        return $userProfile;
    }

    #[Route('/profile', name: 'profile', methods: ['GET', 'POST'])] // Profile Page
    function index(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, UserPasswordHasherInterface $passwordHasher, UsersRepository $usersRepository) : Response
    {
        $user = $session->get("User");

        if ($user === null) // If user not logged in
        {
            return $this->redirectToRoute("login");
        }

        $alert = array();

        $pages = $this->utilitiesService->getPages();
        $pageArray = array();
        $pageArray["CurrentPage"] = "Profile";

        $form = $this->createForm(EditProfileFormType::class);
        $form->handleRequest($request);

        $tokenForm = $this->createForm(ApiTokenFormType::class, null, ["token" => $user->getApiToken() ?? '']);
        $tokenForm->handleRequest($request);

        if ($tokenForm->isSubmitted() && $tokenForm->isValid())
        {
            // TOKEN RESET
            $tokenFormData = $tokenForm->getData();
            if ($tokenForm->get('Refresh')->isClicked()) {
                $user = $this->secureService->setAPITokenForUser($user->getId());
            }
            elseif ($tokenForm->get("Delete")->isClicked())
            {
                $userObject = $this->usersRepository->findUserByUserId($user->getId());
                $userObject->setApiToken(null);

                $entityManager->persist($userObject);
                $entityManager->flush();

                $user = $userObject;
            }

            $tokenForm = $this->createForm(ApiTokenFormType::class, null, ["token" => $user->getApiToken() ?? '']);
        }
        $session->set("User", $user);

        if ($form->isSubmitted() && $form->isValid()) // If symfony form has been submitted.
        {
            try
            {
                $user = $this->handleProfileUpdate($entityManager, $usersRepository, $passwordHasher, $session, $form);
                $alert = $this->utilitiesService->createAlert("success", "Successfully updated your profile!"); // Alert user of success
            }
            catch (\Exception $e) // If profile update failed
            {
                $alert = $this->utilitiesService->createAlert("danger", "Unable to update profile!"); // Alert user of failure
            }

        }

        $session->set("User", $user);

        $pageArray["ApiTokenForm"] = $tokenForm->createView(); // Twiggable form view
        $pageArray["EditProfileForm"] = $form->createView(); // Twiggable form view

        return new Response($this->render("profile.phtml.twig", array_merge($this->utilitiesService->getPageEssentials($session), $pageArray, $alert)));
    }

}
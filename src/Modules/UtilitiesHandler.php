<?php

namespace App\Modules;
use App\Entity\Review;
use App\Entity\UserType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UtilitiesHandler
{
    function __construct()
    {

    }

    // MECHANICS

    public function generateRandomString($Length)
    {
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));

        $String = "";

        for ($i = 0; $i < $Length; $i++) {
            $String = $String . $characters[rand(0, sizeof($characters) - 1)];
        }

        //echo strlen($String);

        return $String;
    }

    public function getPageAuthorisedUserTypes(EntityManagerInterface $entityManager, $pageName, $toString)
    {
        $coreInfo = json_decode(file_get_contents("src/Static/Json/Core.json"), true);
        $pageInfo = $coreInfo["Pages"][$pageName];

        $result = array();
        $_authorisedUserTypes = null;

        $qb2 = $entityManager->createQueryBuilder();
        $qb2->select('r')
            ->from(UserType::class, 'r');


        $count = 0;
        foreach($pageInfo["UserTypes"] as $userTypeName)
        {
            $qb2->orWhere("r.name = :usertype" . $count)
                ->setParameter("usertype" . $count, $userTypeName);

            $count++;
        }

        $_authorisedUserTypes = $qb2->getQuery()->getResult();

        foreach ($_authorisedUserTypes as $userType)
        {
            if ($toString)
            {
                array_push($result, $userType->getName());
            }
            else
            {
                array_push($result, $userType);
            }
        }

        //foreach($pageInfo["UserTypes"] as $userTypeName)
        //{
        //    array_push($_authorisedUserTypes, $entityManager->find(UserType::class, $userTypeName));
        //}

        return $result;
    }

    public function getPageEssentials(SessionInterface $session)
    {
        $coreInfo = json_decode(file_get_contents("src/Static/Json/Core.json"), true);

        $PagesInfo = $coreInfo["Pages"];
        $SiteInfo = $coreInfo["Site"];

        $UserObject = null;

        if ($session->get("User") !== null)
        {
            $UserObject = array(
                "username" => $session->get("User")->getUsername(),
                "profilepictureurl" => $session->get("User")->getProfilePictureURL(),
                "usertype" => array(
                    "name" => $session->get("User")->getUserType()->getName(),
                    "level" => $session->get("User")->getUserType()->getLevel()
                )
            );
        }

        return array("Pages" => $PagesInfo, "Site" => $SiteInfo, "User" => $UserObject);
    }

    public function createAlert(string $type, string $message)
    {
        return array("Alert" => array("Type" => $type, "Message" => $message));
    }

    public function getGenres()
    {
        $coreInfo = json_decode(file_get_contents("src/Static/Json/Core.json"), true);
        return $coreInfo["Genres"];
    }

    public function getUserTypes()
    {
        $coreInfo = json_decode(file_get_contents("src/Static/Json/Core.json"), true);
        return $coreInfo["UserTypes"];
    }

    public function getPages()
    {
        $coreInfo = json_decode(file_get_contents("src/Static/Json/Core.json"), true);
        return $coreInfo["Pages"];
    }
}

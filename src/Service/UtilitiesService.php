<?php

namespace App\Service;
use App\Entity\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class UtilitiesService
{
    private $IpService;

    function __construct(IpService $IpService)
    {
        $this->IpService = $IpService;
    }

    // MECHANICS
    public function getTick()
    {
        return round(microtime(true));
    }

    public function clamp($value, $min, $max) {
        return max($min, min($max, $value));
    }

    public function generateRandomString($Length)
    {
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));

        $String = "";

        for ($i = 0; $i < $Length; $i++) {
            $String = $String . $characters[rand(0, sizeof($characters) - 1)];
        }

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

        $ServerInfo = array(
            "Region" => $this->IpService->getRegion()
        );

        $UserObject = null;

        if ($session->get("User") !== null)
        {
            $UserObject = array(
                "userId" => $session->get("User")->getId(),
                "username" => $session->get("User")->getUsername(),
                "profilepictureurl" => $session->get("User")->getProfilePictureURL(),
                "usertype" => array(
                    "name" => $session->get("User")->getUserType()->getName(),
                    "level" => $session->get("User")->getUserType()->getLevel()
                )
            );
        }

        return array("Pages" => $PagesInfo, "Site" => $SiteInfo, "User" => $UserObject, "Server" => $ServerInfo);
    }

    public function createAlert(string $type, string $message)
    {
        return array("Alert" => array("Type" => $type, "Message" => $message));
    }

    public function getMaxItemsPerPage()
    {
        $coreInfo = json_decode(file_get_contents("src/Static/Json/Core.json"), true);
        return $coreInfo["Site"]["MaxItemsPerPage"] ?? 0;
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

    public function log($message)
    {
        $message = date("H:i:s") . " - $message - ".PHP_EOL;
        print($message);
        flush();
        ob_flush();
    }
}

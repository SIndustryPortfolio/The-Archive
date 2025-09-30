<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Genre;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Dotenv\Dotenv;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Contracts\Cache\CacheInterface;

class IpService extends AbstractController
{
    private $client;
    private $cache;


    public function __construct(CacheInterface $cache)
    {
        $this->client = new Client(["base_uri" => "http://ip-api.com/"]);
        $this->cache = $cache;

        //$this->region = $this->getRegion();

        $this->getRegion();
    }



    public function getRegion()
    {
        $response = null;

        $regionItem = $this->cache->getItem("server-region");


        if (!$regionItem->isHit())
        {
            $response = $regionItem->get();
        }


        if ($response !== null)
        {
            return $response;
        }

        try
        {
            $requestResponse = $this->client->request("GET", "json/", [
                "headers" => [
                    "Content-Type" => "application/json"
                ]
            ]);

            $requestResponse = json_decode($requestResponse->getBody()->getContents(), true);

            if ($requestResponse and isset($requestResponse["country"]))
            {
                $response = $requestResponse["country"];
            }
        }
        catch (RequestException $e)
        {
            $response = "?";
        }

        //$this->region = $response;

        $regionItem->set($response);
        $regionItem->expiresAfter(3600); // Hour
        $this->cache->save($regionItem);

        return $response;
    }

}
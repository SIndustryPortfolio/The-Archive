<?php


namespace App\Service;

use App\Entity\Book;
use App\Entity\Genre;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Dotenv\Dotenv;

use Google\Auth\OAuth2;
use Google\Auth\Credentials\ServiceAccountCredentials;


//use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleGeminiService
{
    private $apiKey;
    private $client;

    private $credentials;
    private $accessToken;

    public function __construct()
    {
        $this->client = new Client(["base_uri" => "https://generativelanguage.googleapis.com/"]);
        $this->credentials = new ServiceAccountCredentials("https://www.googleapis.com/auth/cloud-platform", "src/Secret/gen-lang-client-0634435812-6304803c388e.json");


        $this->apiKey = $_ENV["GOOGLE_GEMINI_API_KEY"];
        $this->accessToken = $this->credentials->fetchAuthToken()["access_token"];

    }

    public function GetRandomBookTitle()
    {
        $response = null;

        try {
            $requestResponse = $this->client->request("POST", "v1beta/models/gemini-2.0-flash:generateContent", [
               "query" => ["key" => $this->apiKey],
                "headers" => [
                    "Content-Type" => "application/json",
                ],
                "json" => [
                    "contents" => [
                        "parts" => [
                            ["text" => "Output nothing but a singular completely random existing book title in plain text with no quotations"],
                        ]
                    ]
                ]
            ]);

            $requestResponse = json_decode($requestResponse->getBody()->getContents(), true);

            if ($requestResponse and !empty($requestResponse["candidates"][0]["content"]["parts"]))
            {
                $response = $requestResponse["candidates"][0]["content"]["parts"][0]["text"];
            }

        } catch (RequestException $e) {
            $response = "";
        }


        return $response;
    }

}
<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Genre;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Dotenv\Dotenv;

class DiscordService
{
    private $channelToURL;
    private $client;

    public function __construct()
    {
        $this->client = new Client(["base_uri" => "https://ptb.discord.com/api/webhooks/"]);

        $this->channelToURL = array(
          "SearchLogs" => $_ENV["SEARCH_LOGGER_URL"]
        );
    }

    public function sendMessage($channelName, $message)
    {
        $webhookURL = $this->channelToURL[$channelName];
        $response = null;

        $data = [
            "content" => "",
            "embeds" => [$message]
        ];

        try
        {
            $response = $this->client->request("POST", $webhookURL, [
                "json" => $data,
                "headers" => [
                    "Content-Type" => "application/json"
                ]
            ]);
        }
        catch (RequestException $e)
        {
            $response = false;
        }

        return $response;
    }

}
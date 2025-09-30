<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Genre;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use function Webmozart\Assert\Tests\StaticAnalysis\lower;

//use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleBooksService extends AbstractController
{
    private $client;
    private UtilitiesService $utilitiesService;
    private $cache;



    public function __construct(UtilitiesService $utilitiesService, EntityManagerInterface $entityManager, CacheInterface $cache)
    {

        // Functions
        // INIT
        $this->utilitiesService = $utilitiesService; //new UtilitiesService();
        $this->client = new Client(["base_uri" => "https://www.googleapis.com/books/v1/"]);
        $this->cache = $cache;
    }

    public function getBookFromItem(array $item)
    {
        // CORE
        $volumeInfo = $item["volumeInfo"];

        // Functions
        // INIT
        $book = new Book();
        $genre = new Genre();

        $genre->setName($volumeInfo["categories"][0] ?? "?");
        $book->setTitle($volumeInfo["title"] ?? "?");
        $book->setIsExternal(true);

        $authors = "";
        foreach ($volumeInfo["authors"] ?? [] as $author)
        {
            $authors .= $author . ", ";
        }

        if ($authors == "")
        {
            $authors = "?";
        }

        $book->setAuthor($authors);

        if (isset($volumeInfo["imageLinks"])) {
            $book->setImageURL($volumeInfo["imageLinks"]["smallThumbnail"] ?? "");
        }
        else
        {
            $book->setImageURL("");
        }

        $book->setDescription($volumeInfo["description"] ?? "?");
        $book->setGenre($genre);

        return $book;
    }

    public function searchBooks(string $title, int $pageNumber, $genreFilterArray): array
    {
        // Functions
        // INIT
        $genreQuery = "";

        if ($genreFilterArray != null and !empty($genreFilterArray))
        {
            $genreQuery = "+" . implode("+OR+subject:", array_map("urlencode", $genreFilterArray));

        }

        $cacheItem = $this->cache->getItem("google-books-" . strtolower($title) . strtolower($genreQuery));

        $cacheData = null;

        if ($cacheItem->isHit())
        {
            $cacheData = $cacheItem->get();
        }


        $searchQuery = "intitle:" . urlencode($title) . $genreQuery;

        $response = $cacheData;

        try
        {
            if ($response == null)
            {
                $response = $this->client->request("GET", "volumes", [
                    "query" => [
                        "q" => $searchQuery,
                        "startIndex" => (($pageNumber ?? 1) - 1) * $this->utilitiesService->getMaxItemsPerPage(),
                        "maxResults" => $this->utilitiesService->getMaxItemsPerPage()
                    ],
                ]);

                $response = json_decode($response->getBody()->getContents(), true);

                $cacheItem->set($response);
                $cacheItem->expiresAfter(3600); // Hour
                $this->cache->save($cacheItem);
            }
        }
        catch (RequestException $e)
        {
            $response = [];
        }



        return $response;
    }

    public function getBookById(string $iD) : array
    {
        // Functions
        // INIT
        $response = $this->client->request("GET", "volumes/{$iD}");

        return json_decode($response->getBody()->getContents(), true);
    }

}
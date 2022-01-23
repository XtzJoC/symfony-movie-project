<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OMBdAPIConnector
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getMovieFromAPI(string $movieName): ?array
    {
        $response = $this->client->request(
            'GET',
            'http://www.omdbapi.com/?apikey=d5031f44&t='.$movieName
        );

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->toArray();

        $result = NULL;

        if($content['Response'] == "True"){
            $result = [
                'name' => $content['Title'],
                'description' => $content['Plot'],
                'imgURL' => $content['Poster'],
            ];
        }

        return $result;
    }
}
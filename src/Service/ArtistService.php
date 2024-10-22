<?php

namespace App\Service;

use App\Entity\Artist;
use App\Factory\ArtistFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class ArtistService
{
    private string $token;
    public function __construct(private readonly AuthSpotifyService  $authSpotifyService,
                                private readonly HttpClientInterface $httpClient,
                                private readonly ArtistFactory        $artistFactory
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    public function query(string $query): array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . $query . '&type=artist&locale=fr-FR', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
            return $this->artistFactory->createMultipleFromSpotifyData($response->toArray()['artists']['items']);
        } catch (Throwable $t) {
            print($t->getMessage());
            return [];
        }
    }
    public function get(string $id): ?Artist
    {
        try {
            $responseDetails = $this->httpClient->request('GET', 'https://api.spotify.com/v1/artists/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
            return $this->artistFactory->createSingleFromSpotifyData($responseDetails->toArray());
        } catch (Throwable $t) {
            print($t->getMessage());
            return null;
        }
    }
}

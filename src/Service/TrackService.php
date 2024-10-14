<?php

namespace App\Service;

use App\Entity\Track;
use App\Factory\TrackFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrackService
{
    private string $token;

    public function __construct(private readonly AuthSpotifyService  $authSpotifyService,
                                private readonly HttpClientInterface $httpClient,
                                private readonly TrackFactory        $trackFactory
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    public function query(string $query): array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . $query . '&type=track&locale=fr-FR', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
            return $this->trackFactory->createMultipleFromSpotifyData($response->toArray()['tracks']['items']);
        } catch (\Throwable $t) {
            print($t->getMessage());
            return [];
        }
    }

    public function recommandation(string $trackId): array
    {
        try {
            $responseRecomanded = $this->httpClient->request('GET', 'https://api.spotify.com/v1/recommendations?seed_tracks=' . $trackId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
            return $this->trackFactory->createMultipleFromSpotifyData($responseRecomanded->toArray()['tracks']);
        } catch (\Throwable $t) {
            print($t->getMessage());
            return [];
        }
    }

    public function get(string $id): ?Track
    {
        try {
            $responseDetails = $this->httpClient->request('GET', 'https://api.spotify.com/v1/tracks/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
            return $this->trackFactory->createSingleFromSpotifyData($responseDetails->toArray());
        } catch (\Throwable $t) {
            print($t->getMessage());
            return null;
        }
    }
}

<?php

namespace App\Factory;

use App\Entity\Track;

class TrackFactory
{
    public function __construct(private readonly ArtistFactory $artistFactory) {}
    public function createSingleFromSpotifyData(mixed $items): Track
    {
        return new Track(
            $items['disc_number'] ?? -1,
            $items['duration_ms'] ?? -1,
            $items['explicit'] ?? false,
            $items['external_ids']['isrc'] ?? false,
            $items['external_urls']['spotify'] ?? "",
            $items['href'] ?? "",
            $items['id'] ?? "",
            $items['is_local'] ?? false,
            $items['name'] ?? "",
            $items['popularity'] ?? -1,
            $items['preview_url'] ?? null,
            $items['track_number'] ?? -1,
            $items['uri'] ?? "",
            $items['album']['images'][0]["url"] ?? null,
            $this->artistFactory->createMultipleFromSpotifyData($items['artists']) ?? [],
        );
    }

    public function createMultipleFromSpotifyData(mixed $items): array
    {
        $result = [];
        for ($i = 0; $i < count($items); $i++) {
            if (is_array($items[$i])) {
                $result[] = $this->createSingleFromSpotifyData($items[$i]);
            }
        }
        return $result;
    }
}
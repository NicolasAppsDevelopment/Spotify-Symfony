<?php

namespace App\Factory;

use App\Entity\Artist;

readonly class ArtistFactory
{
    public function createSingleFromSpotifyData(mixed $items): Artist
    {
        return new Artist(
            $items['id'] ?? "",
            $items['name'] ?? "",
            $items['images'][0]['url'] ?? null,
            $items['popularity'] ?? null,
        );
    }

    public function createMultipleFromSpotifyData(mixed $items): array
    {
        $result = [];
        for ($i = 0; $i < count($items); $i++) {
            $result[] = $this->createSingleFromSpotifyData($items[$i]);
        }
        return $result;
    }
}
<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Track;
use App\Factory\ArtistFactory;
use App\Factory\TrackFactory;
use App\Repository\TrackRepository;
use App\Service\AuthSpotifyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FavoriteController extends AbstractController
{
    private string $token;

    public function __construct(private readonly AuthSpotifyService  $authSpotifyService,
                                private readonly HttpClientInterface $httpClient,
                                private readonly TrackFactory        $trackFactory,
                                private readonly ArtistFactory        $artistFactory
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }
    #[Route('/favorite', name: 'app_favorite_index')]
    public function index(UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        $favoriteTracks = $entityManager->getRepository(Track::class)->findBy(
            ['isFavorite' => true]
        );
        $favoriteArtists = $entityManager->getRepository(Artist::class)->findBy(
            ['isFavorite' => true]
        );

        return $this->render('favorite/index.html.twig', [
            'tracks' => $favoriteTracks,
            'artists' => $favoriteArtists,
        ]);
    }

    #[Route('/favorite/add/track', name: 'app_favorite_add_track')]
    public function addTrack(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get("id");

        // Check if the track is already in the database
        $trackInDB = $entityManager->getRepository(Track::class)->findOneBy(['id' => $id]);
        if ($trackInDB) {
            // Just update the existing track
            $trackInDB->setIsFavorite(true);
        } else {
            // Fetch the track from Spotify and create a new entry in the database
            $responseDetails = $this->httpClient->request('GET', 'https://api.spotify.com/v1/tracks/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
            $trackDetails = $this->trackFactory->createSingleFromSpotifyData($responseDetails->toArray());
            $trackDetails->setIsFavorite(true);

            // Add artists if not in the database
            foreach ($trackDetails->getArtists() as $artist) {
                $artistInDB = $entityManager->getRepository(Artist::class)->findOneBy(['id' => $artist->getId()]);
                if (!$artistInDB) {
                    $entityManager->persist($artist);
                } else {
                    $trackDetails->removeArtist($artist);
                    $trackDetails->addArtist($artistInDB);
                }
            }
            $entityManager->persist($trackDetails);
        }

        $entityManager->flush();
        return new Response("Track added");
    }

    #[Route('/favorite/remove/track', name: 'app_favorite_remove_track')]
    public function removeTrack(Request $request, EntityManagerInterface $entityManager, TrackRepository $trackRepository): Response
    {
        $id = $request->get("id");
        $track = $entityManager->getRepository(Track::class)->findOneBy(
            ['id' => $id, 'isFavorite' => true]
        );

        // Remove associated artists if
        // - they are not in the favorite list
        // - they are not associated with any other track
        $artists = $track->getArtists();
        foreach ($artists as $artist) {
            $anotherTrack = $entityManager->getConnection()->executeQuery(
                'SELECT * FROM track_artist WHERE artist_id = :artistId AND track_id != :trackId;',
                ['artistId' => $artist->getId(), 'trackId' => $track->getId()]
            )->fetchOne();
            if (!$artist->isFavorite() && !$anotherTrack) {
                $entityManager->remove($artist);
            }
        }

        $entityManager->remove($track);

        // Remove the many-to-many relationship
        $entityManager->getConnection()->executeQuery('DELETE FROM track_artist WHERE track_id = :trackId;', ['trackId' => $id]);

        $entityManager->flush();

        return new Response("Track removed");
    }

    #[Route('/favorite/add/artist', name: 'app_favorite_add_artist')]
    public function addArtist(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get("id");

        // Check if the artist is already in the database
        $artistInDB = $entityManager->getRepository(Artist::class)->findOneBy(['id' => $id]);
        if ($artistInDB) {
            // Just update the existing artist
            $artistInDB->setIsFavorite(true);
        } else {
            // Fetch the artist from Spotify and create a new entry in the database
            $responseDetails = $this->httpClient->request('GET', 'https://api.spotify.com/v1/artists/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
            $artistDetails = $this->artistFactory->createSingleFromSpotifyData($responseDetails->toArray());
            $artistDetails->setIsFavorite(true);
            $entityManager->persist($artistDetails);
        }

        $entityManager->flush();
        return new Response("Artist added");
    }

    #[Route('/favorite/remove/artist', name: 'app_favorite_remove_artist')]
    public function removeArtist(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get("id");
        $artist = $entityManager->getRepository(Track::class)->findOneBy(
            ['id' => $id, 'isFavorite' => true]
        );

        // Check if the artist is associated with any track
        $tracks = $entityManager->getRepository(Track::class)->findOneBy(
            ['artists' => $artist]
        );
        if ($tracks) {
            // Just exclude the artist from the favorite list
            $artist->setIsFavorite(false);
        } else {
            // Remove the artist
            $entityManager->remove($artist);
        }

        $entityManager->flush();
        return new Response("Artist removed");
    }

    #[Route('/favorite/remove', name: 'app_favorite_remove')]
    public function clear(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Clear all tracks and artists from db
        foreach ($entityManager->getRepository(Track::class)->findAll() as $track) {
            $entityManager->remove($track);
        }
        foreach ($entityManager->getRepository(Artist::class)->findAll() as $artist) {
            $entityManager->remove($artist);
        }

        $entityManager->getConnection()->executeQuery('DELETE FROM track_artist WHERE 1 = 1;');

        $entityManager->flush();
        return new Response("Favorite list cleared");
    }
}

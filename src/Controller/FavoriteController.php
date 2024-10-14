<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Track;
use App\Entity\User;
use App\Factory\ArtistFactory;
use App\Factory\TrackFactory;
use App\Service\ArtistService;
use App\Service\TrackService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FavoriteController extends AbstractController
{
    public function __construct(private readonly TrackService  $trackService,
                                private readonly ArtistService  $artistService,
                                private readonly HttpClientInterface $httpClient,
                                private readonly TrackFactory        $trackFactory,
                                private readonly ArtistFactory        $artistFactory
    ) {}
    #[Route('/favorite', name: 'app_favorite_index')]
    public function index(UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // get User
        $userInDB = $entityManager->getRepository(User::class)->getUserById($user->getUserIdentifier());
        if (!$userInDB) {
            return new Response("Not authorized", 401);
        }

        $favoriteTracks = $userInDB->getBookmarkedTracks();
        $favoriteArtists = $userInDB->getBookmarkedArtists();

        return $this->render('favorite/index.html.twig', [
            'tracks' => $favoriteTracks,
            'artists' => $favoriteArtists,
        ]);
    }

    #[Route('/favorite/add/track/{id}', name: 'app_favorite_add_track')]
    public function addTrack(string $id, UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // get User
        $userInDB = $entityManager->getRepository(User::class)->getUserById($user->getUserIdentifier());
        if (!$userInDB) {
            return new Response("Not authorized", 401);
        }

        // Check if the track is already in the database
        $trackInDB = $entityManager->getRepository(Track::class)->getTrackById($id);
        if ($trackInDB) {
            $isFavorite = $userInDB->getBookmarkedTracks()->contains($trackInDB);
            if (!$isFavorite) {
                $userInDB->addBookmarkedTrack($trackInDB);
            }
        } else {
            // Fetch the track from Spotify and create a new entry in the database
            $track = $this->trackService->get($id);
            if (!$track) {
                return new Response("Not found", 404);
            }

            $userInDB->addBookmarkedTrack($track);
            $entityManager->persist($track);
        }

        $entityManager->flush();
        return new Response("Track added");
    }

    #[Route('/favorite/remove/track/{id}', name: 'app_favorite_remove_track')]
    public function removeTrack(string $id, UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // get User
        $userInDB = $entityManager->getRepository(User::class)->getUserById($user->getUserIdentifier());
        if (!$userInDB) {
            return new Response("Not authorized", 401);
        }

        // Check if the track is already in the database
        $trackInDB = $entityManager->getRepository(Track::class)->getTrackById($id);
        if ($trackInDB) {
            $isFavorite = $userInDB->getBookmarkedTracks()->contains($trackInDB);
            if ($isFavorite) {
                $userInDB->removeBookmarkedTrack($trackInDB);
            }

            if ($trackInDB->getBookmarkedBy()->count() === 0) {
                $entityManager->remove($trackInDB);
            }
        }

        $entityManager->flush();

        return new Response("Track removed");
    }

    #[Route('/favorite/add/artist/{id}', name: 'app_favorite_add_artist')]
    public function addArtist(string $id, UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // get User
        $userInDB = $entityManager->getRepository(User::class)->getUserById($user->getUserIdentifier());
        if (!$userInDB) {
            return new Response("Not authorized", 401);
        }

        // Check if the artist is already in the database
        $artistInDB = $entityManager->getRepository(Artist::class)->getArtistById($id);

        if ($artistInDB) {
            $isFavorite = $userInDB->getBookmarkedArtists()->contains($artistInDB);
            if (!$isFavorite) {
                $userInDB->addBookmarkedArtist($artistInDB);
            }
        } else {
            // Fetch the artist from Spotify and create a new entry in the database
            $artist = $this->artistService->get($id);
            if (!$artist) {
                return new Response("Not found", 404);
            }

            $userInDB->addBookmarkedArtist($artist);
            $entityManager->persist($artist);
        }

        $entityManager->flush();
        return new Response("Track added");
    }

    #[Route('/favorite/remove/artist/{id}', name: 'app_favorite_remove_artist')]
    public function removeArtist(string $id, UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // get User
        $userInDB = $entityManager->getRepository(User::class)->getUserById($user->getUserIdentifier());
        if (!$userInDB) {
            return new Response("Not authorized", 401);
        }

        // Check if the artist is already in the database
        $artistInDB = $entityManager->getRepository(Artist::class)->getArtistById($id);
        if ($artistInDB) {
            $isFavorite = $userInDB->getBookmarkedArtists()->contains($artistInDB);
            if ($isFavorite) {
                $userInDB->removeBookmarkedArtist($artistInDB);
            }

            if ($artistInDB->getBookmarkedBy()->count() === 0) {
                $entityManager->remove($artistInDB);
            }
        }

        $entityManager->flush();

        return new Response("Artist removed");
    }
}

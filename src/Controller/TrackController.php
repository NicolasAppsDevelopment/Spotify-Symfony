<?php

namespace App\Controller;

use App\Entity\Track;
use App\Factory\TrackFactory;
use App\Service\AuthSpotifyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrackController extends AbstractController
{
    private string $token;

    public function __construct(private readonly AuthSpotifyService  $authSpotifyService,
                                private readonly HttpClientInterface $httpClient,
                                private readonly TrackFactory        $trackFactory
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/track', name: 'app_track_index')]
    public function index(Request $request): Response
    {
        $tracks = [];

        $defaultData = ['query' => ''];
        $form = $this->createFormBuilder($defaultData)
            ->add('query', TextType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . $data['query'] . '&type=track&locale=fr-FR', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
            $tracks = $this->trackFactory->createMultipleFromSpotifyData($response->toArray()['tracks']['items']);
        }

        return $this->render('track/index.html.twig', [
            'tracks' => $tracks,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/track/details', name: 'app_track_details')]
    public function details(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get("id");

        $responseDetails = $this->httpClient->request('GET', 'https://api.spotify.com/v1/tracks/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
        $trackDetails = $this->trackFactory->createSingleFromSpotifyData($responseDetails->toArray());

        // Check if the track is already in the database
        $favoriteTrack = $entityManager->getRepository(Track::class)->findOneBy(['id' => $id, 'isFavorite' => true]);
        if ($favoriteTrack) {
            $trackDetails->setIsFavorite(true);
        }

        $responseRecomanded = $this->httpClient->request('GET', 'https://api.spotify.com/v1/recommendations?seed_genres=&seed_artists=' . $trackDetails->getId() . '&seed_tracks=' . $trackDetails->getId(), [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
        $recomandedTracks = $this->trackFactory->createMultipleFromSpotifyData($responseRecomanded->toArray()['tracks']);

        return $this->render('track/details.html.twig', [
            'trackDetails' => $trackDetails,
            'recomandedTracks' => $recomandedTracks,
        ]);
    }
}

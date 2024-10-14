<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Factory\ArtistFactory;
use App\Service\AuthSpotifyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtistController extends AbstractController
{
    private string $token;

    public function __construct(private readonly AuthSpotifyService  $authSpotifyService,
                                private readonly HttpClientInterface $httpClient,
                                private readonly ArtistFactory        $artistFactory
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/artist', name: 'app_artist_index')]
    public function index(Request $request): Response
    {
        $artists = [];

        $defaultData = ['query' => ''];
        $form = $this->createFormBuilder($defaultData)
            ->add('query', TextType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . $data['query'] . '&type=artist&locale=fr-FR', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
            $artists = $this->artistFactory->createMultipleFromSpotifyData($response->toArray()['artists']['items']);
        }

        return $this->render('artist/index.html.twig', [
            'artists' => $artists,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/artist/details', name: 'app_artist_details')]
    public function details(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get("id");

        $responseDetails = $this->httpClient->request('GET', 'https://api.spotify.com/v1/artists/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
        $artistDetails = $this->artistFactory->createSingleFromSpotifyData($responseDetails->toArray());

        $favoriteArtist = $entityManager->getRepository(Artist::class)->findOneBy(['id' => $artistDetails->getId(), 'isFavorite' => true]);
        if ($favoriteArtist) {
            $artistDetails->setIsFavorite(true);
        }

        return $this->render('artist/details.html.twig', [
            'artistDetails' => $artistDetails,
        ]);
    }
}

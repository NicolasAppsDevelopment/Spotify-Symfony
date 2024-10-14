<?php

namespace App\Controller;

use App\Entity\Track;
use App\Entity\User;
use App\Factory\TrackFactory;
use App\Service\TrackService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrackController extends AbstractController
{
    public function __construct(private readonly TrackService  $trackService,
                                private readonly HttpClientInterface $httpClient,
                                private readonly TrackFactory        $trackFactory
    ) {}

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
            $tracks = $this->trackService->query($data['query']);
        }

        return $this->render('track/index.html.twig', [
            'tracks' => $tracks,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/track/{id}', name: 'app_track_details')]
    public function details(string $id, UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // get User
        $userInDB = $entityManager->getRepository(User::class)->getUserById($user->getUserIdentifier());
        if (!$userInDB) {
            return new Response("Not authorized", 401);
        }

        $track = $this->trackService->get($id);
        if (!$track) {
            return new Response("Not found", 404);
        }

        // Check if the track is already in the database
        $isFavorite = $entityManager->getRepository(Track::class)->isBookmarkedByUser($id, $user->getUserIdentifier());
        if ($isFavorite) {
            $track->setIsFavorite(true);
        }

        $recommendedTracks = $this->trackService->recommandation($track->getId());

        return $this->render('track/details.html.twig', [
            'track' => $track,
            'recommendedTracks' => $recommendedTracks,
        ]);
    }
}

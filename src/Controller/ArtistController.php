<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\User;
use App\Factory\ArtistFactory;
use App\Service\ArtistService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtistController extends AbstractController
{
    public function __construct(private readonly ArtistService       $artistService,
                                private readonly HttpClientInterface $httpClient,
                                private readonly ArtistFactory       $artistFactory
    ) {}

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
            $artists = $this->artistService->query($data['query']);
        }

        return $this->render('artist/index.html.twig', [
            'artists' => $artists,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/artist/{id}', name: 'app_artist_details')]
    public function details(string $id, UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // get User
        $userInDB = $entityManager->getRepository(User::class)->findOneBy(['id' => $user->getUserIdentifier()]);
        if (!$userInDB) {
            return new Response("Not authorized", 401);
        }

        $artist = $this->artistService->get($id);
        if (!$artist) {
            return new Response("Not found", 404);
        }

        $isFavorite = $entityManager->getRepository(Artist::class)->isBookmarkedByUser($id, $user->getUserIdentifier());
        if ($isFavorite) {
            $artist->setIsFavorite(true);
        }

        return $this->render('artist/details.html.twig', [
            'artist' => $artist,
        ]);
    }
}

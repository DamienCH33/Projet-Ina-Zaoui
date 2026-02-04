<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository,
        private MediaRepository $mediaRepository,
        private UserRepository $userRepository,
    ) {
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    #[Route('/guests', name: 'guests')]
    public function guests(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $guestsRows = $this->userRepository->findActiveGuestsPaginated($page, 10);

        $guestsData = [];

        foreach ($guestsRows as $row) {
            $guestsData[] = [
                'guest' => [
                    'id' => $row['id'],
                    'name' => $row['name'],
                ],
                'activeMediasCount' => (int) $row['activeMediasCount'],
            ];
        }

        $totalPages = $page + 1;

        return $this->render('front/guests.html.twig', [
            'guestsData' => $guestsData,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id): Response
    {
        $guest = $this->userRepository->findOneBy([
            'id' => $id,
            'isActive' => true,
        ]);

        if (null === $guest) {
            throw $this->createNotFoundException('Invité introuvable ou désactivé.');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest,
        ]);
    }

    #[Route('/portfolio/{id?}', name: 'portfolio')]
    public function portfolio(?int $id = null): Response
    {
        $albums = $this->albumRepository->findAll();
        $album = $id ? $this->albumRepository->find($id) : null;
        $user = $this->userRepository->findOneBy(['admin' => true]);

        $medias = $album
            ? $this->mediaRepository->findBy(['album' => $album])
            : $this->mediaRepository->findBy(['user' => $user]);

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias,
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}

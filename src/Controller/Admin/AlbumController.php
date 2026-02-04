<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Media;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/album')]
class AlbumController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository,
        private MediaRepository $mediaRepository,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/', name: 'admin_album_index')]
    public function index(): Response
    {
        $albums = $this->albumRepository->findAll();

        return $this->render('admin/album/index.html.twig', [
            'albums' => $albums,
        ]);
    }

    #[Route('/add', name: 'admin_album_add')]
    public function add(Request $request): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($album);
            $this->em->flush();

            $this->addFlash('success', 'L’album a bien été ajouté.');

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update/{id}', name: 'admin_album_update')]
    public function update(Request $request, int $id): Response
    {
        $album = $this->albumRepository->find($id);

        if (!$album) {
            $this->addFlash('error', 'Album introuvable.');

            return $this->redirectToRoute('admin_album_index');
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'L’album a bien été mis à jour.');

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_album_delete')]
    public function delete(int $id): Response
    {
        $album = $this->albumRepository->find($id);

        if (!$album) {
            $this->addFlash('error', 'Album introuvable.');

            return $this->redirectToRoute('admin_album_index');
        }

        /** @var Media[] $medias */
        $medias = $this->mediaRepository->findBy(['album' => $album]);

        /** @var string $projectDir */
        $projectDir = $this->getParameter('kernel.project_dir');

        foreach ($medias as $media) {
            $path = $media->getPath();

            $filePath = $projectDir.'/public/'.$path;

            if (is_file($filePath)) {
                unlink($filePath);
            }

            $this->em->remove($media);
        }

        $this->em->remove($album);
        $this->em->flush();

        $this->addFlash('success', 'L’album et ses médias associés ont bien été supprimés.');

        return $this->redirectToRoute('admin_album_index');
    }
}

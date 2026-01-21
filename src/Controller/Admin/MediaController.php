<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/media')]
class MediaController extends AbstractController
{
    public function __construct(private MediaRepository $mediaRepository, private EntityManagerInterface $em) {}

    #[Route('/', name: 'admin_media_index')]
    public function index(Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $criteria = [];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $this->mediaRepository->findBy(
            $criteria,
            ['id' => 'ASC'],
            25,
            25 * ($page - 1)
        );
        $total = $this->mediaRepository->count([]);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }

    #[Route('/add', name: 'admin_media_add')]
    public function add(Request $request)
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, [
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $file->move($uploadDir, $filename);

                $media->setPath('uploads/' . $filename);
            }

            if (!$this->isGranted('ROLE_ADMIN')) {
                $media->setUser($this->getUser());
            }

            $this->em->persist($media);
            $this->em->flush();

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_media_delete')]
    public function delete(int $id)
    {
        $media = $this->mediaRepository->find($id);

        if (!$media) {
            $this->addFlash('error', 'Le média demandé est introuvable.');
            return $this->redirectToRoute('admin_media_index');
        }

        $path = $this->getParameter('kernel.project_dir') . '/public/' . $media->getPath();
        if (file_exists($path) && is_file($path)) {
            unlink($path);
        }

        $this->em->remove($media);
        $this->em->flush();

        $this->addFlash('success', 'Le média a bien été supprimé.');

        return $this->redirectToRoute('admin_media_index');
    }
}

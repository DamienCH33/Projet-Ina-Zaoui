<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\GuestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/guest')]
class GuestController extends AbstractController
{
    public function __construct(private UserRepository $userRepository, private EntityManagerInterface $em,  private UserPasswordHasherInterface $passwordHasher) {}
    #[Route('/', name: 'admin_guest_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $criteria = [];
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $total = $this->userRepository->count($criteria);
        $totalPages = (int) ceil($total / $limit);

        $guests = $this->userRepository->findBy(
            $criteria,
            ['id' => 'ASC'],
            $limit,
            $limit * ($page - 1)
        );

        return $this->render('admin/guest/index.html.twig', [
            'guests' => $guests,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/add/{id}', name: 'admin_guest_add')]
    public function add(Request $request, ?int $id = null): Response
    {
        $guest = $id ? $this->userRepository->find($id) : new User();
        $form = $this->createForm(UserType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $guest->setAdmin(false);
            $guest->setIsActive(true);

            if (!$guest->getPassword()) {
                $hashed = $this->passwordHasher->hashPassword($guest, 'guest');
                $guest->setPassword($hashed);
            }

            if (!$guest->getId()) {
                $this->em->persist($guest);
            }

            $this->em->flush();

            $message = $id ? 'Invité modifié avec succès.' : 'Invité ajouté avec succès.';
            $this->addFlash('success', $message);

            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/add.html.twig', [
            'form' => $form->createView(),
            'guest' => $guest,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_guest_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $guest = $this->userRepository->find($id);
        if (!$guest) {
            $this->addFlash('error', 'Invité introuvable.');
            return $this->redirectToRoute('admin_guest_index');
        }

        $this->em->remove($guest);
        $this->em->flush();

        $this->addFlash('success', 'Invité supprimé avec tout son contenu.');
        return $this->redirectToRoute('admin_guest_index');
    }

    #[Route('/toggle/{id}', name: 'admin_guests_toggle', methods: ['POST'])]
    public function toggle(int $id): Response
    {
        $guest = $this->userRepository->find($id);
        if (!$guest) {
            $this->addFlash('error', 'Invité introuvable.');
            return $this->redirectToRoute('admin_guest_index');
        }

        $guest->setIsActive(!$guest->isActive());
        $this->em->flush();

        $status = $guest->isActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "L'invité a été $status.");

        return $this->redirectToRoute('admin_guest_index');
    }
}

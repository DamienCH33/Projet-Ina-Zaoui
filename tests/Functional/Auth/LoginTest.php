<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);
        $this->hasher = $hasher;

        // Nettoyage de la table user
        $this->entityManager
            ->createQuery('DELETE FROM App\Entity\User')
            ->execute();

        // Création admin
        $admin = new User();
        $admin->setName('Ina Zaoui');
        $admin->setEmail('ina@zaoui.com');
        $admin->setPassword(
            $this->hasher->hashPassword($admin, 'password')
        );
        $admin->setAdmin(true);
        $admin->setIsActive(true);

        $this->entityManager->persist($admin);

        // Création invité
        $guest = new User();
        $guest->setName('Invité 1');
        $guest->setEmail('invite+1@example.com');
        $guest->setPassword(
            $this->hasher->hashPassword($guest, 'password')
        );
        $guest->setAdmin(false);
        $guest->setIsActive(true);

        $this->entityManager->persist($guest);
        $this->entityManager->flush();
    }

    public function testAdminCanAccessDashboard(): void
    {
        $admin = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina@zaoui.com']);

        self::assertNotNull($admin, 'L’utilisateur admin doit exister.');

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/guest/');

        $this->assertResponseIsSuccessful(
            'L’admin doit pouvoir accéder à la page des invités.'
        );
        $this->assertSelectorExists('h1');
        $this->assertSelectorTextContains('h1', 'Admin');
    }

    public function testGuestCannotAccessAdminDashboard(): void
    {
        $guest = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'invite+1@example.com']);

        self::assertNotNull($guest, 'L’utilisateur invité doit exister.');

        $this->client->loginUser($guest);
        $this->client->request('GET', '/admin/guest/');

        $this->assertResponseStatusCodeSame(
            403,
            'Un invité ne doit pas avoir accès à la zone admin.'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}

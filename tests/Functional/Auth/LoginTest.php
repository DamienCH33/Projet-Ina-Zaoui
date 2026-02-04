<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $hasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        $manager->createQuery('DELETE FROM App\Entity\User')->execute();

        $admin = new User();
        $admin->setName('Ina Zaoui');
        $admin->setEmail('ina@zaoui.com');
        $admin->setPassword($hasher->hashPassword($admin, 'password'));
        $admin->setAdmin(true);
        $admin->setIsActive(true);
        $manager->persist($admin);

        $guest = new User();
        $guest->setName('Invité 1');
        $guest->setEmail('invite+1@example.com');
        $guest->setPassword($hasher->hashPassword($guest, 'password'));
        $guest->setAdmin(false);
        $guest->setIsActive(true);
        $manager->persist($guest);

        $manager->flush();
    }

    public function testAdminCanAccessDashboard(): void
    {
        $admin = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina@zaoui.com']);

        $this->assertNotNull($admin, 'L’utilisateur admin doit exister.');

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/guest/');

        $this->assertResponseIsSuccessful('L’admin doit pouvoir accéder à la page des invités.');
        $this->assertSelectorExists('h1');
        $this->assertSelectorTextContains('h1', 'Admin');
    }

    public function testGuestCannotAccessAdminDashboard(): void
    {
        $guest = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'invite+1@example.com']);

        $this->assertNotNull($guest, 'L’utilisateur invité doit exister.');

        $this->client->loginUser($guest);
        $this->client->request('GET', '/admin/guest/');

        $this->assertResponseStatusCodeSame(403, 'Un invité ne doit pas avoir accès à la zone admin.');
    }
}

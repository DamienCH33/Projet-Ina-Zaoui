<?php

declare(strict_types=1);

namespace App\Tests\Functional\Guest;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class GuestControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
{
    $this->client = static::createClient();
    $container = static::getContainer();
    $this->entityManager = $container->get('doctrine')->getManager();

    $loader = new Loader();
    $loader->addFixture(new \App\DataFixtures\AppFixtures($container->get(UserPasswordHasherInterface::class)));

    $purger = new ORMPurger($this->entityManager);
    $executor = new ORMExecutor($this->entityManager, $purger);
    $executor->execute($loader->getFixtures());

    $admin = $this->entityManager->getRepository(User::class)
        ->findOneBy(['email' => 'ina@zaoui.com']);
    $this->client->loginUser($admin);
}
    private function createGuest(string $name, string $email): User
    {
        $guest = new User();
        $guest->setName($name);
        $guest->setEmail($email);
        $guest->setPassword(password_hash('Password', PASSWORD_BCRYPT));
        $guest->setIsActive(true);
        $guest->setAdmin(false);

        $this->entityManager->persist($guest);
        $this->entityManager->flush();

        return $guest;
    }

    private function createAdmin(string $email): User
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$admin) {
            $admin = new User();
            $admin->setName('Admin Test');
            $admin->setEmail($email);
            $admin->setPassword(password_hash('Password', PASSWORD_BCRYPT));
            $admin->setIsActive(true);
            $admin->setAdmin(true);

            $this->entityManager->persist($admin);
            $this->entityManager->flush();
        }

        return $admin;
    }
    public function testAdminCanViewGuestList(): void
    {
        $guestName = 'Invité Test';
        $this->createGuest($guestName, 'guest' . uniqid() . '@test.com');

        $crawler = $this->client->request('GET', '/admin/guest/');

        $mainH1 = $crawler->filter('main h1');
        $this->assertCount(1, $mainH1, 'Il devrait y avoir un h1 dans main');

        $guestCards = $crawler->filter('.card .guest-info strong');
        $guestNames = array_map(fn($node) => trim($node->textContent), iterator_to_array($guestCards));

        $this->assertContains($guestName, $guestNames, 'Le guest créé doit apparaître dans la liste');
    }
    public function testAdminCanAddGuest(): void
    {
        $crawler = $this->client->request('GET', '/admin/guest/add');

        $uniqueEmail = 'newguest_' . uniqid() . '@test.com';

        $form = $crawler->selectButton('Ajouter')->form([
            'user[name]' => 'Nouvel Invité',
            'user[email]' => $uniqueEmail,
            'user[description]' => 'Description test',
            'user[isActive]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            'La requête doit être redirigée après ajout.'
        );

        $crawler = $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success', 'Le flash message de succès doit exister.');
        $this->assertSelectorTextContains('.alert-success', 'Invité ajouté avec succès.');

        $guest = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $uniqueEmail]);

        $this->assertNotNull($guest, 'L’invité doit exister en base après ajout.');
        $this->assertFalse($guest->isAdmin(), 'L’invité ajouté ne doit pas être admin.');
        $this->assertTrue($guest->isActive(), 'L’invité ajouté doit être actif.');
    }
    public function testAdminCanToggleGuest(): void
    {
        $guest = $this->createGuest('Invité Toggle', 'toggle@test.com');

        $admin = $this->createAdmin('admin@test.com');

        $this->client->loginUser($admin);

        $crawler = $this->client->request('GET', '/admin/guest/');

        $form = $crawler->filter('form[action$="/toggle/' . $guest->getId() . '"]')->form();

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            'La requête doit être redirigée après toggle.'
        );

        $this->client->followRedirect();

        $toggledGuest = $this->entityManager->getRepository(User::class)->find($guest->getId());
        $this->assertFalse($toggledGuest->isActive(), 'Le guest doit être désactivé après toggle.');
    }
    public function testAdminCanDeleteGuest(): void
    {
        $guest = $this->createGuest('Invité Suppr', 'delete@test.com');
        $guestId = $guest->getId();

        $admin = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'ina@zaoui.com']);
        $this->client->loginUser($admin);

        $this->client->request(
            'POST',
            '/admin/guest/delete/' . $guestId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded']
        );

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            'La requête doit être redirigée après suppression.'
        );

        $this->client->followRedirect();

        $deletedGuest = $this->entityManager->getRepository(User::class)->find($guestId);
        $this->assertNull($deletedGuest, 'L’invité doit être supprimé de la base.');
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\Album;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AlbumControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

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

        $loader = new Loader();
        $loader->addFixture(new AppFixtures($hasher));

        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());

        $admin = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina@zaoui.com']);

        self::assertNotNull($admin, 'L’utilisateur admin doit exister pour les tests.');

        $this->client->loginUser($admin);
    }

    /** Vérifie que la page d’index s’affiche correctement */
    public function testIndexDisplaysAlbums(): void
    {
        $this->client->request('GET', '/admin/album/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('main', 'La page index doit afficher un contenu principal.');
    }

    /** Test d’ajout d’un album valide */
    public function testAddAlbumSuccessfully(): void
    {
        $crawler = $this->client->request('GET', '/admin/album/add');

        $form = $crawler->selectButton('Ajouter')->form([
            'album[name]' => 'Nouvel Album Test',
        ]);

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            'La soumission valide doit rediriger.'
        );

        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success', 'Un message de succès doit s’afficher.');

        $album = $this->entityManager
            ->getRepository(Album::class)
            ->findOneBy(['name' => 'Nouvel Album Test']);

        $this->assertNotNull($album, 'L’album doit exister en base.');
    }

    /** Test d’ajout avec formulaire invalide */
    public function testAddAlbumInvalidForm(): void
    {
        $crawler = $this->client->request('GET', '/admin/album/add');

        $form = $crawler->selectButton('Ajouter')->form([
            'album[name]' => '',
        ]);

        $this->client->submit($form);

        $this->assertFalse(
            $this->client->getResponse()->isRedirect(),
            'Le formulaire invalide ne doit pas rediriger.'
        );

        $this->assertSelectorExists(
            '.form-error, .invalid-feedback, ul li',
            'Une erreur doit être affichée pour le formulaire invalide.'
        );
    }

    /** Test de mise à jour d’un album existant */
    public function testUpdateExistingAlbum(): void
    {
        $album = new Album();
        $album->setName('Album Original');
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $crawler = $this->client->request(
            'GET',
            '/admin/album/update/'.$album->getId()
        );

        $form = $crawler->selectButton('Modifier')->form([
            'album[name]' => 'Album Modifié',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertSelectorExists(
            '.alert-success',
            'Un message de succès doit s’afficher après la mise à jour.'
        );

        $updated = $this->entityManager
            ->getRepository(Album::class)
            ->find($album->getId());

        self::assertNotNull($updated);
        $this->assertSame('Album Modifié', $updated->getName());
    }

    /** Test de mise à jour d’un album inexistant */
    public function testUpdateNonExistingAlbumRedirectsWithError(): void
    {
        $this->client->request('GET', '/admin/album/update/999999');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertSelectorExists(
            '.alert-error, .alert-danger',
            'Un message d’erreur doit apparaître.'
        );
    }

    /** Test de suppression d’un album existant */
    public function testDeleteExistingAlbum(): void
    {
        $album = new Album();
        $album->setName('Album à supprimer');
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $albumId = $album->getId();

        $this->client->request('GET', '/admin/album/delete/'.$albumId);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $deleted = $this->entityManager
            ->getRepository(Album::class)
            ->find($albumId);

        $this->assertNull($deleted, 'L’album doit être supprimé.');
    }

    /** Test de suppression d’un album inexistant */
    public function testDeleteNonExistingAlbumShowsError(): void
    {
        $this->client->request('GET', '/admin/album/delete/999999');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $this->assertSelectorExists(
            '.alert-error, .alert-danger',
            'Un message d’erreur doit s’afficher.'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}

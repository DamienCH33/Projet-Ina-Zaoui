<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class MediaControllerTest extends WebTestCase
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

        self::assertNotNull($admin, 'L’utilisateur admin doit exister.');
        $this->client->loginUser($admin);
    }

    /** Test d’affichage de la page index */
    public function testIndexDisplaysMediaList(): void
    {
        $this->client->request('GET', '/admin/media/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(
            'main',
            'La page index doit contenir la liste des médias.'
        );
    }

    /** Test formulaire invalide (fichier manquant) */
    public function testAddMediaInvalidForm(): void
    {
        $crawler = $this->client->request('GET', '/admin/media/add');

        $form = $crawler->selectButton('Ajouter')->form([
            'media[title]' => '',
        ]);

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect());
        $this->assertSelectorExists(
            '.form-error, .invalid-feedback, ul li',
            'Une erreur doit être affichée pour un formulaire invalide.'
        );
    }

    /** Test de suppression d’un média existant */
    public function testDeleteExistingMedia(): void
    {
        $media = new Media();
        $media->setTitle('À supprimer');
        $media->setPath('uploads/test_delete.jpg');

        /** @var string $projectDir */
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        $filePath = $projectDir.'/public/uploads/test_delete.jpg';

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        file_put_contents($filePath, 'fake data');

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina@zaoui.com']);

        self::assertNotNull($user);
        $media->setUser($user);

        $this->entityManager->persist($media);
        $this->entityManager->flush();

        $mediaId = $media->getId();

        $this->client->request('GET', '/admin/media/delete/'.$mediaId);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $deleted = $this->entityManager
            ->getRepository(Media::class)
            ->find($mediaId);

        $this->assertNull($deleted, 'Le média doit être supprimé.');
        $this->assertFileDoesNotExist(
            $filePath,
            'Le fichier physique doit être supprimé.'
        );
    }

    /** Test de suppression d’un média inexistant */
    public function testDeleteNonExistingMedia(): void
    {
        $this->client->request('GET', '/admin/media/delete/999999');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $this->assertSelectorExists(
            '.alert-error, .alert-danger',
            'Un message d’erreur doit être affiché.'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}

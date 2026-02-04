<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(UserRepository::class);
    }

    
    // test pour FindActiveGuestsPaginated
  
    public function testFindActiveGuestsPaginated(): void
    {
        $page = 1;
        $limit = 10;

        $guests = $this->repository->findActiveGuestsPaginated($page, $limit);

        $this->assertIsArray($guests, 'La méthode doit retourner un tableau.');
        foreach ($guests as $guest) {
            $this->assertArrayHasKey('id', $guest);
            $this->assertArrayHasKey('name', $guest);
            $this->assertArrayHasKey('activeMediasCount', $guest);
            $this->assertIsInt($guest['activeMediasCount']);
        }
    }

    // Test pour findOneByEmail
    public function testFindByEmail(): void
    {
        $email = 'ina@zaoui.com';
        $user = $this->repository->findOneByEmail($email);

        $this->assertInstanceOf(User::class, $user, 'Doit retourner un objet User.');
        $this->assertSame($email, $user->getEmail(), 'L’email de l’utilisateur doit correspondre.');
    }

    // Test pour findAll
    public function testFindAll(): void
    {
        $users = $this->repository->findAll();

        $this->assertIsArray($users, 'La méthode doit retourner un tableau.');
        $this->assertNotEmpty($users, 'La liste des utilisateurs ne doit pas être vide.');
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    // Test pour find() par ID
    public function testFindById(): void
    {
        $allUsers = $this->repository->findAll();
        if (!empty($allUsers)) {
            $userId = $allUsers[0]->getId();
            $user = $this->repository->find($userId);

            $this->assertInstanceOf(User::class, $user);
            $this->assertSame($userId, $user->getId());
        } else {
            $this->markTestSkipped('Pas d’utilisateur en base pour tester find() par ID.');
        }
    }

    // Test findBy avec filtre admin
    public function testFindByAdmin(): void
    {
        $admins = $this->repository->findBy(['admin' => true]);

        $this->assertIsArray($admins);
        foreach ($admins as $admin) {
            $this->assertInstanceOf(User::class, $admin);
            $this->assertTrue($admin->isAdmin());
        }
    }

    // Test findOneBy email inexistant
    public function testFindOneByNonExistentEmail(): void
    {
        $user = $this->repository->findOneBy(['email' => 'inexistant@example.com']);
        $this->assertNull($user);
    }


    public function testUpgradePasswordWithMock(): void
    {
        $user = new User();
        $user->setPassword('old_password');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($user);
        $em->expects($this->once())->method('flush');

        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityManager'])
            ->getMock();
        $repository->method('getEntityManager')->willReturn($em);

        $newPassword = 'new_password';
        $repository->upgradePassword($user, $newPassword);

        $this->assertSame($newPassword, $user->getPassword());
    }
}

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

        /** @var UserRepository $repository */
        $repository = static::getContainer()->get(UserRepository::class);
        $this->repository = $repository;
    }

    public function testFindActiveGuestsPaginated(): void
    {
        $guests = $this->repository->findActiveGuestsPaginated(1, 10);

        foreach ($guests as $guest) {
            $this->assertArrayHasKey('id', $guest);
            $this->assertArrayHasKey('name', $guest);
            $this->assertArrayHasKey('activeMediasCount', $guest);
        }
    }

    public function testFindByEmail(): void
    {
        $email = 'ina@zaoui.com';

        $user = $this->repository->findOneBy(['email' => $email]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($email, $user->getEmail());
    }

    public function testFindAll(): void
    {
        $users = $this->repository->findAll();

        $this->assertNotEmpty($users);
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    public function testFindById(): void
    {
        $users = $this->repository->findAll();

        if ([] === $users) {
            $this->markTestSkipped('Pas d’utilisateur en base.');
        }

        $userId = $users[0]->getId();
        $user = $this->repository->find($userId);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($userId, $user->getId());
    }

    public function testFindByAdmin(): void
    {
        $admins = $this->repository->findBy(['admin' => true]);

        foreach ($admins as $admin) {
            $this->assertInstanceOf(User::class, $admin);
            $this->assertTrue($admin->isAdmin());
        }
    }

    public function testFindOneByNonExistentEmail(): void
    {
        $user = $this->repository->findOneBy(['email' => 'inexistant@example.com']);
        $this->assertNull($user);
    }

    public function testUpgradePassword(): void
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

        $repository->upgradePassword($user, 'new_password');

        $this->assertSame('new_password', $user->getPassword());
    }
}

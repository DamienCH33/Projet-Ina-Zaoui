<?php

namespace App\Tests\Unit\Factory;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserFactoryTest extends KernelTestCase
{
    use Factories;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testUserFactoryHashesPassword(): void
    {
        /** @var \App\Entity\User $user */
        $user = UserFactory::createOne();

        $this->assertNotEquals('password', $user->getPassword(), 'Le mot de passe ne doit pas être en clair.');
        $this->assertTrue(password_verify('password', $user->getPassword()), 'Le mot de passe hashé doit correspondre au mot de passe original.');
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testDefaultAdminIsFalse(): void
    {
        $user = new User();
        $this->assertFalse($user->isAdmin(), 'Par défaut, un utilisateur ne doit pas être admin.');
    }

    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $user->setEmail('invite@example.com');
        $this->assertSame('invite@example.com', $user->getEmail());
    }

    public function testSetAndGetName(): void
    {
        $user = new User();
        $user->setName('Invité Test');
        $this->assertSame('Invité Test', $user->getName());
    }

    public function testRolesAlwaysContainsUserRole(): void
    {
        $user = new User();
        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles, 'Tout utilisateur doit avoir le rôle ROLE_USER par défaut.');
    }

    public function testIsActiveDefaultTrue(): void
    {
        $user = new User();
        $this->assertTrue($user->isActive(), 'Par défaut, un invité doit être actif.');
    }
}

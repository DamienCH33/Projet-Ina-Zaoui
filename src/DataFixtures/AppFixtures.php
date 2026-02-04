<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setName('Ina Zaoui');
        $admin->setEmail('ina@zaoui.com');
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $admin->setAdmin(true);
        $admin->setIsActive(true);
        $manager->persist($admin);

        $guest = new User();
        $guest->setName('Invité 1');
        $guest->setEmail('invite+1@example.com');
        $guest->setPassword($this->hasher->hashPassword($guest, 'password'));
        $guest->setAdmin(false);
        $guest->setIsActive(true);
        $manager->persist($guest);

        $manager->flush();
    }
}

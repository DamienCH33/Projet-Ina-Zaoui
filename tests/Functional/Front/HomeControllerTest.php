<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testHomePageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h2.home-title', 'La page d’accueil doit contenir le titre Photographe');
    }

    public function testAboutPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/about');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('main', 'La page about doit contenir un main');
    }
    public function testPortfolioPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/portfolio');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('main', 'La page portfolio doit contenir un main');
    }
}

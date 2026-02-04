<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class AccessTest extends FunctionalTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testGuestCannotAccessAdminRoutes(): void
    {
        $guest = $this
            ->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['email' => 'invite+1@example.com']);

        self::assertNotNull(
            $guest,
            'Le guest invite+1@example.com doit exister dans les fixtures.'
        );

        $this->client->loginUser($guest);

        $restrictedRoutes = [
            ['/admin/guest', 'GET'],
            ['/admin/guest/add', 'GET'],
            ['/admin/guest/delete/1', 'POST'],
            ['/admin/guest/toggle/1', 'POST'],
        ];

        foreach ($restrictedRoutes as [$url, $method]) {
            $this->client->request($method, $url);
            $this->assertResponseStatusCodeSame(
                403,
                "L’invité ne doit pas accéder à {$url}"
            );
        }
    }

    public function testAdminHasAccess(): void
    {
        $admin = $this
            ->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina@zaoui.com']);

        self::assertNotNull(
            $admin,
            'L’admin ina@zaoui.com doit exister dans les fixtures.'
        );

        $this->client->loginUser($admin);

        $adminRoutes = [
            '/admin/guest',
            '/admin/guest/add',
        ];

        foreach ($adminRoutes as $route) {
            $this->client->request('GET', $route);

            if ($this->client->getResponse()->isRedirection()) {
                $this->client->followRedirect();
            }

            $this->assertResponseIsSuccessful(
                "L’admin doit pouvoir accéder à {$route}"
            );
        }
    }
}

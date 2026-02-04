<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User;
use App\Tests\Functional\FunctionalTestCase;

final class AccessTest extends FunctionalTestCase
{
    public function testGuestCannotAccessAdminRoutes(): void
    {
        $client = static::createClient();

        $guest = $client->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'invite+1@example.com']);

        $this->assertNotNull($guest, 'Le guest invite+1@example.com doit exister dans les fixtures.');

        $client->loginUser($guest);

        $restrictedRoutes = [
            ['/admin/guest', 'GET'],
            ['/admin/guest/add', 'GET'],
            ['/admin/guest/delete/1', 'POST'],
            ['/admin/guest/toggle/1', 'POST'],
        ];

        foreach ($restrictedRoutes as [$url, $method]) {
            $client->request($method, $url);
            $this->assertResponseStatusCodeSame(403, "L’invité ne doit pas accéder à {$url}");
        }
    }
    public function testAdminHasAccess(): void
    {
        $client = static::createClient();

        $admin = $client->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina@zaoui.com']);

        $this->assertNotNull($admin, 'L’admin ina@zaoui.com doit exister dans les fixtures.');

        $client->loginUser($admin);

        $adminRoutes = [
            '/admin/guest',
            '/admin/guest/add',
        ];

        foreach ($adminRoutes as $route) {
            $client->request('GET', $route);

            if ($client->getResponse()->isRedirection()) {
                $client->followRedirect();
            }

            $this->assertResponseIsSuccessful("L’admin doit pouvoir accéder à {$route}");
        }
    }
}

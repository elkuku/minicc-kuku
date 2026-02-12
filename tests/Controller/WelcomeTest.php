<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WelcomeTest extends WebTestCase
{
    public function testWelcomePageIsAccessibleForAdmin(): void
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
        $this->assertInstanceOf(User::class, $admin);
        $client->loginUser($admin);

        $client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('welcome');
    }

    public function testWelcomeAnonymousRedirectsToLogin(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('welcome');
    }

    public function testWelcomeForRegularUser(): void
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('welcome');
    }
}

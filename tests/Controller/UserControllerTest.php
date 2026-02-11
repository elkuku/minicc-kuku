<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
        self::assertNotNull($admin);
        $this->client->loginUser($admin);
    }

    public function testUserIndex(): void
    {
        $this->client->request('GET', '/users');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_index');
    }

    public function testUserIndexFilterActive(): void
    {
        $this->client->request('GET', '/users?user_active=1');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_index');
    }

    public function testUserIndexFilterInactive(): void
    {
        $this->client->request('GET', '/users?user_active=0');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_index');
    }

    public function testUserCreateGetForm(): void
    {
        $this->client->request('GET', '/users/create');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_create');
    }

    public function testUserEditGetForm(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);

        $this->client->request('GET', '/users/edit/' . $user->getId());

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_edit');
    }

    public function testUserIndexDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/users');

        self::assertResponseStatusCodeSame(403);
    }
}

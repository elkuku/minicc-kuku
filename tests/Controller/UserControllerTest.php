<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
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
        $this->assertInstanceOf(User::class, $admin);
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

    public function testUserIndexFilterAll(): void
    {
        $this->client->request('GET', '/users?user_active=all');

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
        $this->assertInstanceOf(User::class, $user);

        $this->client->request('GET', '/users/edit/' . $user->getId());

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_edit');
    }

    public function testUserCreatePostValidForm(): void
    {
        $crawler = $this->client->request('GET', '/users/create');
        $form = $crawler->filter('button[type="submit"]')->form([
            'user_full[name]' => 'New Test User',
            'user_full[email]' => 'newuser@example.com',
            'user_full[gender]' => '1',
            'user_full[inqCi]' => '1234567890',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('users_index');
    }

    public function testUserEditPostValidForm(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user2@example.com']);
        $this->assertInstanceOf(User::class, $user);

        $crawler = $this->client->request('GET', '/users/edit/' . $user->getId());
        $form = $crawler->filter('button[type="submit"]')->form([
            'user_full[name]' => 'Updated User',
            'user_full[email]' => 'user2@example.com',
            'user_full[gender]' => '2',
            'user_full[inqCi]' => '0987654321',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('users_index');
    }

    public function testUserIndexDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request('GET', '/users');

        self::assertResponseStatusCodeSame(403);
    }
}

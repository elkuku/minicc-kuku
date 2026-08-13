<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
        $this->assertInstanceOf(User::class, $admin);
        $this->client->loginUser($admin);
    }

    public function testUserIndex(): void
    {
        $this->client->request(Request::METHOD_GET, '/users');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_index');
    }

    public function testUserIndexFilterActive(): void
    {
        $this->client->request(Request::METHOD_GET, '/users?user_active=1');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_index');
    }

    public function testUserIndexFilterInactive(): void
    {
        $this->client->request(Request::METHOD_GET, '/users?user_active=0');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_index');
    }

    public function testUserIndexFilterAll(): void
    {
        $this->client->request(Request::METHOD_GET, '/users?user_active=all');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_index');
    }

    public function testUserCreateGetForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/users/create');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_create');
    }

    public function testUserEditGetForm(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);

        $this->client->request(Request::METHOD_GET, '/users/edit/' . $user->getId());

        self::assertResponseIsSuccessful();
        self::assertRouteSame('users_edit');
    }

    public function testUserCreatePostValidForm(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/users/create');
        $form = $crawler->filter('form[name="user_full"] button[type="submit"]')->form([
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
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user2@example.com']);
        $this->assertInstanceOf(User::class, $user);

        $crawler = $this->client->request(Request::METHOD_GET, '/users/edit/' . $user->getId());
        $form = $crawler->filter('form[name="user_full"] button[type="submit"]')->form([
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

    public function testUserEditWithBlankInqCiFailsValidationInsteadOfCrashing(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);

        $crawler = $this->client->request(Request::METHOD_GET, '/users/edit/' . $user->getId());
        $form = $crawler->filter('form[name="user_full"] button[type="submit"]')->form([
            'user_full[name]' => 'Some Name',
            'user_full[email]' => $user->getEmail(),
            'user_full[gender]' => '1',
            'user_full[inqCi]' => '',
        ]);
        $this->client->submit($form);

        // Blank inqCi must fail NotBlank validation and re-render the form (422),
        // not crash with a 500 (User::setInqCi() takes a non-nullable string).
        self::assertResponseStatusCodeSame(422);
    }

    public function testUserEditWithRoleFieldOmittedDoesNotCrashOrEscalate(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $userId = $user->getId();

        $crawler = $this->client->request(Request::METHOD_GET, '/users/edit/' . $userId);
        $token = $crawler->filter('input[name="user_full[_token]"]')->attr('value');
        $this->assertIsString($token);

        // A raw POST that omits the 'role' key entirely (as a non-browser
        // client could easily send, bypassing the <select>'s UI constraints)
        // must not crash (User::setRole() takes a non-nullable UserRole) or
        // silently escalate privilege - it should fall back to ROLE_USER.
        $this->client->request(Request::METHOD_POST, '/users/edit/' . $userId, [
            'user_full' => [
                'isActive' => '1',
                'gender' => '1',
                'name' => 'X',
                'email' => $user->getEmail(),
                'inqCi' => '123',
                '_token' => $token,
            ],
        ]);

        self::assertResponseRedirects();

        $reloaded = $userRepository->find($userId);
        $this->assertInstanceOf(User::class, $reloaded);
        self::assertSame('ROLE_USER', $reloaded->getRole()->value);
    }

    public function testUserIndexDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/users');

        self::assertResponseStatusCodeSame(403);
    }
}

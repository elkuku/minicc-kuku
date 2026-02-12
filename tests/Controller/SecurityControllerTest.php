<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
{
    public function testGoogleVerifyEndpointExists(): void
    {
        $client = self::createClient();

        // POST to the Google verify endpoint - it throws UnexpectedValueException
        // which results in a 500, but the route exists and is handled
        $client->request(Request::METHOD_POST, '/connect/google/verify');

        $statusCode = $client->getResponse()->getStatusCode();
        // Should not be 404 - route exists. May be 500 (exception) or redirect
        $this->assertNotSame(404, $statusCode);
    }

    public function testLoginPageIsAccessible(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/login');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('login');
    }

    public function testLogoutRedirects(): void
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/logout');

        self::assertResponseRedirects();
    }
}

<?php

namespace App\Tests;

use App\Repository\UserRepository;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StoreAccessTest extends WebTestCase
{
    public function testAnonymousAccess(): void
    {
        $client = static::createClient();

        $client->request('GET', '/stores/1');
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();

        try {
            self::assertSelectorTextContains('h2', 'Login');
        } catch (ExpectationFailedException $exception) {
            file_put_contents('gugu.html', $crawler->html());

            throw $exception;
        }
    }

    public function testAccessWrongUser(): void
    {
        $client = static::createClient();
        /**
         * @var UserRepository $userRepository
         */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'user2@example.com']);
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/stores/1');
        try {
            self::assertSelectorTextContains('small', 'Forbidden');
        } catch (ExpectationFailedException $exception) {
            file_put_contents('guguxx.html', $crawler->html());

            throw $exception;
        }
    }

    public function testAccess(): void
    {
        $client = static::createClient();
        /**
         * @var UserRepository $userRepository
         */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' =>'user1@example.com']);
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/stores/1');
        try {
            self::assertSelectorTextContains('h2', 'Transacciones');
        } catch (ExpectationFailedException $exception) {
            file_put_contents('guguyy.html', $crawler->html());

            throw $exception;
        }
    }
}

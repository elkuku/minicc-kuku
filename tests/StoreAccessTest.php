<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\UserRepository;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StoreAccessTest extends WebTestCase
{
    public function testAnonymousAccess(): void
    {
        $client = static::createClient();

        $client->request('GET', '/stores/1');
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();

        try {
            self::assertSelectorTextContains('h2', 'Login');
        } catch (ExpectationFailedException $expectationFailedException) {
            file_put_contents('gugu.html', $crawler->html());

            throw $expectationFailedException;
        }
    }

    public function testAccessWrongUser(): void
    {
        $client = static::createClient();
        /**
         * @var UserRepository $userRepository
         */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $testUser = $userRepository
            ->findOneBy([
                'email' => 'user2@example.com',
            ]);
        if (! $testUser) {
            throw new \UnexpectedValueException('User not found.');
        }

        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/stores/1');
        try {
            self::assertSelectorTextContains('small', 'Forbidden');
        } catch (ExpectationFailedException $expectationFailedException) {
            file_put_contents('guguxx.html', $crawler->html());

            throw $expectationFailedException;
        }
    }

    public function testAccess(): void
    {
        $client = static::createClient();
        /**
         * @var UserRepository $userRepository
         */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $testUser = $userRepository
            ->findOneBy([
                'email' => 'user1@example.com',
            ]);
        if (! $testUser) {
            throw new \UnexpectedValueException('User not found.');
        }

        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/stores/1');
        try {
            self::assertSelectorTextContains('h2', 'Transacciones');
        } catch (ExpectationFailedException $expectationFailedException) {
            file_put_contents('guguyy.html', $crawler->html());

            throw $expectationFailedException;
        }
    }
}

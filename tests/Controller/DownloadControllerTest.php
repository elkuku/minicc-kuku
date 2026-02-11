<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DownloadControllerTest extends WebTestCase
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

    public function testDownloadUsersListRequiresAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/download/users-list');

        self::assertResponseStatusCodeSame(403);
    }

    public function testDownloadUsersRucListRequiresAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/download/users-ruc-list');

        self::assertResponseStatusCodeSame(403);
    }

    public function testStoreTransactionsRequiresStoreAccess(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);
        /** @var StoreRepository $storeRepository */
        $storeRepository = static::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        self::assertNotNull($store);
        $storeId = $store->getId();

        $client->request('GET', '/download/store-transactions/' . $storeId . '/2024');

        // May fail due to wkhtmltopdf not being available, but should not be 403
        $statusCode = $client->getResponse()->getStatusCode();
        self::assertNotSame(403, $statusCode);
    }

    public function testStoreTransactionsDeniedForWrongUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user2@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);
        /** @var StoreRepository $storeRepository */
        $storeRepository = static::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        self::assertNotNull($store);
        $storeId = $store->getId();

        $client->request('GET', '/download/store-transactions/' . $storeId . '/2024');

        self::assertResponseStatusCodeSame(403);
    }

    public function testDownloadPlanillasRequiresAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/download/planillas');

        self::assertResponseStatusCodeSame(403);
    }

    public function testDownloadTransactionsRequiresAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/download/transactions');

        self::assertResponseStatusCodeSame(403);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Store;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DownloadControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
        $this->assertInstanceOf(User::class, $admin);
        $client->loginUser($admin);
    }

    public function testDownloadUsersListRequiresAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/download/users-list');

        self::assertResponseStatusCodeSame(403);
    }

    public function testDownloadUsersRucListRequiresAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/download/users-ruc-list');

        self::assertResponseStatusCodeSame(403);
    }

    public function testStoreTransactionsRequiresStoreAccess(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);
        /** @var StoreRepository $storeRepository */
        $storeRepository = static::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        $this->assertInstanceOf(Store::class, $store);
        $storeId = $store->getId();

        $client->request(Request::METHOD_GET, '/download/store-transactions/' . $storeId . '/2024');

        // May fail due to wkhtmltopdf not being available, but should not be 403
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertNotSame(403, $statusCode);
    }

    public function testStoreTransactionsDeniedForWrongUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user2@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);
        /** @var StoreRepository $storeRepository */
        $storeRepository = static::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        $this->assertInstanceOf(Store::class, $store);
        $storeId = $store->getId();

        $client->request(Request::METHOD_GET, '/download/store-transactions/' . $storeId . '/2024');

        self::assertResponseStatusCodeSame(403);
    }

    public function testDownloadPlanillasRequiresAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/download/planillas');

        self::assertResponseStatusCodeSame(403);
    }

    public function testDownloadTransactionsRequiresAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/download/transactions');

        self::assertResponseStatusCodeSame(403);
    }
}

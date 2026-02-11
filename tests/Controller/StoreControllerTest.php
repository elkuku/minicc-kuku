<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StoreControllerTest extends WebTestCase
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

    public function testStoreIndex(): void
    {
        $this->client->request('GET', '/stores');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_index');
    }

    public function testStoreCreateGetForm(): void
    {
        $this->client->request('GET', '/stores/create');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_create');
    }

    public function testStoreEditGetForm(): void
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = static::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        self::assertNotNull($store);
        $storeId = $store->getId();

        $this->client->request('GET', '/stores/edit/' . $storeId);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_edit');
    }

    public function testStoreTransactionsForOwner(): void
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

        $client->request('GET', '/stores/' . $storeId);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_transactions');
    }

    public function testStoreTransactionsForAdmin(): void
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = static::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        self::assertNotNull($store);
        $storeId = $store->getId();

        $this->client->request('GET', '/stores/' . $storeId);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_transactions');
    }

    public function testStoreTransactionsForWrongUser(): void
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

        $client->request('GET', '/stores/' . $storeId);

        self::assertResponseStatusCodeSame(403);
    }

    public function testStoreTransactionsWithYearFilter(): void
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = static::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        self::assertNotNull($store);
        $storeId = $store->getId();

        $this->client->request('GET', '/stores/' . $storeId . '?year=2024');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_transactions');
    }

    public function testStoreIndexDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/stores');

        self::assertResponseStatusCodeSame(403);
    }
}

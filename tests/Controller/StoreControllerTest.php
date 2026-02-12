<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Store;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StoreControllerTest extends WebTestCase
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

    public function testStoreIndex(): void
    {
        $this->client->request(Request::METHOD_GET, '/stores');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_index');
    }

    public function testStoreCreateGetForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/stores/create');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_create');
    }

    public function testStoreEditGetForm(): void
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        $this->assertInstanceOf(Store::class, $store);
        $storeId = $store->getId();

        $this->client->request(Request::METHOD_GET, '/stores/edit/' . $storeId);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_edit');
    }

    public function testStoreCreatePostValidForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/stores/create');
        $this->client->submitForm('Guardar', [
            'store[destination]' => 'NEW-STORE',
            'store[valAlq]' => '500',
            'store[medElectrico]' => '12345',
            'store[medAgua]' => '67890',
        ]);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('stores_index');
    }

    public function testStoreEditPostValidForm(): void
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        $this->assertInstanceOf(Store::class, $store);
        $storeId = $store->getId();

        $this->client->request(Request::METHOD_GET, '/stores/edit/' . $storeId);
        $this->client->submitForm('Guardar', [
            'store[destination]' => 'TEST',
            'store[valAlq]' => '200',
            'store[medElectrico]' => '12345',
            'store[medAgua]' => '67890',
        ]);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('stores_index');
    }

    public function testStoreTransactionsForOwner(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);
        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        $this->assertInstanceOf(Store::class, $store);
        $storeId = $store->getId();

        $client->request(Request::METHOD_GET, '/stores/' . $storeId);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_transactions');
    }

    public function testStoreTransactionsForAdmin(): void
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        $this->assertInstanceOf(Store::class, $store);
        $storeId = $store->getId();

        $this->client->request(Request::METHOD_GET, '/stores/' . $storeId);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_transactions');
    }

    public function testStoreTransactionsForWrongUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user2@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);
        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        $this->assertInstanceOf(Store::class, $store);
        $storeId = $store->getId();

        $client->request(Request::METHOD_GET, '/stores/' . $storeId);

        self::assertResponseStatusCodeSame(403);
    }

    public function testStoreTransactionsWithYearFilter(): void
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        $this->assertInstanceOf(Store::class, $store);
        $storeId = $store->getId();

        $this->client->request(Request::METHOD_GET, '/stores/' . $storeId . '?year=2024');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('stores_transactions');
    }

    public function testStoreIndexDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/stores');

        self::assertResponseStatusCodeSame(403);
    }
}

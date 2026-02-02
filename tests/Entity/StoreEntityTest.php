<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Store;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StoreEntityTest extends KernelTestCase
{
    /**
     * Test that Store entity can be hydrated with nullable userId column.
     *
     * This test ensures that when a Store is persisted without a userId
     * and then loaded from the database, Doctrine can properly hydrate
     * the entity even when the nullable column contains NULL.
     */
    public function testStoreHydrationWithNullableUserId(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);

        $store = new Store();
        $store->setDestination('Test Store');

        $entityManager->persist($store);
        $entityManager->flush();

        $storeId = $store->getId();
        self::assertNotNull($storeId);

        // Clear the entity manager to force reloading from database
        $entityManager->clear();

        // Load the store from database - this will fail if nullable
        // properties are not correctly typed
        $loadedStore = $storeRepository->find($storeId);

        self::assertNotNull($loadedStore);
        self::assertSame('Test Store', $loadedStore->getDestination());
        self::assertNull($loadedStore->getUserId());

        // Cleanup
        $entityManager->remove($loadedStore);
        $entityManager->flush();
    }
}

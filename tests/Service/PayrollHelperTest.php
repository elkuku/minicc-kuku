<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Store;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\PayrollHelper;
use PHPUnit\Framework\TestCase;

final class PayrollHelperTest extends TestCase
{
    public function testGetDataReturnsCorrectFactDateAndPrevDate(): void
    {
        $helper = $this->createHelper([]);

        $result = $helper->getData(2024, 5);

        self::assertSame('2024-5-1', $result['factDate']);
        self::assertSame('2024-4-01', $result['prevDate']);
    }

    public function testGetDataJanuaryWrapsToPreviousYearDecember(): void
    {
        $helper = $this->createHelper([]);

        $result = $helper->getData(2024, 1);

        self::assertSame('2024-1-1', $result['factDate']);
        self::assertSame('2023-12-01', $result['prevDate']);
    }

    public function testGetDataReturnsAllStoresWhenNoFilter(): void
    {
        $store1 = (new Store())->setId(1);
        $store2 = (new Store())->setId(2);

        $helper = $this->createHelper([$store1, $store2]);

        $result = $helper->getData(2024, 6);

        self::assertCount(2, $result['stores']);
        self::assertArrayHasKey(1, $result['storeData']);
        self::assertArrayHasKey(2, $result['storeData']);
    }

    public function testGetDataFiltersStoreById(): void
    {
        $store1 = (new Store())->setId(1);
        $store2 = (new Store())->setId(2);

        $helper = $this->createHelper([$store1, $store2]);

        $result = $helper->getData(2024, 6, 2);

        self::assertCount(1, $result['stores']);
        self::assertSame($store2, $result['stores'][0]);
        self::assertArrayHasKey(2, $result['storeData']);
        self::assertArrayNotHasKey(1, $result['storeData']);
    }

    public function testGetDataStoreDataContainsSaldoIniAndTransactions(): void
    {
        $store = (new Store())->setId(10);

        $transactionRepo = $this->createStub(TransactionRepository::class);
        $transactionRepo->method('getSaldoALaFecha')->willReturn(500.0);
        $transactionRepo->method('findMonthPayments')->willReturn([100.0, 200.0]);

        $storeRepo = $this->createStub(StoreRepository::class);
        $storeRepo->method('findAll')->willReturn([$store]);

        $helper = new PayrollHelper($storeRepo, $transactionRepo);

        $result = $helper->getData(2024, 3);

        self::assertSame(500.0, $result['storeData'][10]['saldoIni']);
        self::assertSame([100.0, 200.0], $result['storeData'][10]['transactions']);
    }

    public function testGetDataDecemberPrevDateIsNovember(): void
    {
        $helper = $this->createHelper([]);

        $result = $helper->getData(2024, 12);

        self::assertSame('2024-12-1', $result['factDate']);
        self::assertSame('2024-11-01', $result['prevDate']);
    }

    public function testGetDataWithEmptyStores(): void
    {
        $helper = $this->createHelper([]);

        $result = $helper->getData(2024, 6);

        self::assertCount(0, $result['stores']);
        self::assertEmpty($result['storeData']);
    }

    public function testGetDataWithStoreIdZeroReturnsAllStores(): void
    {
        $store1 = (new Store())->setId(1);
        $store2 = (new Store())->setId(2);

        $helper = $this->createHelper([$store1, $store2]);

        $result = $helper->getData(2024, 6, 0);

        self::assertCount(2, $result['stores']);
    }

    public function testGetDataFebruaryPrevDateIsJanuary(): void
    {
        $helper = $this->createHelper([]);

        $result = $helper->getData(2024, 2);

        self::assertSame('2024-2-1', $result['factDate']);
        self::assertSame('2024-1-01', $result['prevDate']);
    }

    public function testGetDataFilterWithNonExistentStoreIdReturnsEmpty(): void
    {
        $store1 = (new Store())->setId(1);

        $helper = $this->createHelper([$store1]);

        $result = $helper->getData(2024, 6, 999);

        self::assertCount(0, $result['stores']);
        self::assertEmpty($result['storeData']);
    }

    /**
     * @param Store[] $stores
     */
    private function createHelper(array $stores): PayrollHelper
    {
        $storeRepo = $this->createStub(StoreRepository::class);
        $storeRepo->method('findAll')->willReturn($stores);

        $transactionRepo = $this->createStub(TransactionRepository::class);
        $transactionRepo->method('getSaldoALaFecha')->willReturn(0);
        $transactionRepo->method('findMonthPayments')->willReturn([]);

        return new PayrollHelper($storeRepo, $transactionRepo);
    }
}

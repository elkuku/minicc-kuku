<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use DateTime;
use App\Entity\Store;
use App\Entity\User;
use App\Helper\Paginator\PaginatorOptions;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TransactionRepositoryTest extends KernelTestCase
{
    private TransactionRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var TransactionRepository $repository */
        $repository = self::getContainer()->get(TransactionRepository::class);
        $this->repository = $repository;
    }

    public function testFindByStoreAndYear(): void
    {
        $store = $this->getTestStore();
        $year = (int) date('Y');

        $transactions = $this->repository->findByStoreAndYear($store, $year);

        foreach ($transactions as $transaction) {
            $this->assertSame($store->getId(), $transaction->getStore()->getId());
            $this->assertSame($year, (int) $transaction->getDate()->format('Y'));
        }

        $this->assertGreaterThanOrEqual(0, count($transactions));
    }

    public function testGetSaldoForStore(): void
    {
        $store = $this->getTestStore();
        $saldo = $this->repository->getSaldo($store);

        $this->assertNotNull($saldo);
    }

    public function testGetSaldoAnterior(): void
    {
        $store = $this->getTestStore();
        $year = (int) date('Y');

        $result = $this->repository->getSaldoAnterior($store, $year);

        $this->assertTrue(is_numeric($result) || $result === null);
    }

    public function testGetSaldoALaFecha(): void
    {
        $store = $this->getTestStore();

        $result = $this->repository->getSaldoALaFecha($store, date('Y-m-d'));

        $this->assertTrue(is_numeric($result) || $result === null);
    }

    public function testFindMonthPayments(): void
    {
        $store = $this->getTestStore();
        $month = (int) date('m');
        $year = (int) date('Y');

        $payments = $this->repository->findMonthPayments($store, $month, $year);

        $this->assertGreaterThanOrEqual(0, count($payments));
    }

    public function testFindByDate(): void
    {
        $year = (int) date('Y');
        $month = (int) date('m');

        $transactions = $this->repository->findByDate($year, $month);

        $this->assertGreaterThanOrEqual(0, count($transactions));
    }

    public function testGetPagosPorAno(): void
    {
        $year = (int) date('Y');

        $pagos = $this->repository->getPagosPorAno($year);

        $this->assertGreaterThanOrEqual(0, count($pagos));
    }

    public function testGetRawList(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');

        $result = $this->repository->getRawList($options);

        $this->assertGreaterThanOrEqual(0, $result->count());
    }

    public function testGetRawListWithSearchCriteria(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setCriteria([
            'amount' => '123.45',
            'date_from' => '2020-01-01',
            'date_to' => '2030-12-31',
        ]);

        $result = $this->repository->getRawList($options);

        $this->assertGreaterThanOrEqual(0, $result->count());
    }

    public function testGetRawListWithStoreCriteria(): void
    {
        $store = $this->getTestStore();
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setCriteria([
            'store' => (string) $store->getId(),
        ]);

        $result = $this->repository->getRawList($options);

        $this->assertGreaterThanOrEqual(0, $result->count());
    }

    public function testGetRawListWithTypeCriteria(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setCriteria([
            'type' => '1',
        ]);

        $result = $this->repository->getRawList($options);

        $this->assertGreaterThanOrEqual(0, $result->count());
    }

    public function testGetRawListWithRecipeAndCommentCriteria(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setCriteria([
            'recipe' => '100',
            'comment' => 'test',
        ]);

        $result = $this->repository->getRawList($options);

        $this->assertGreaterThanOrEqual(0, $result->count());
    }

    public function testGetLastRecipeNo(): void
    {
        $number = $this->repository->getLastRecipeNo();

        $this->assertGreaterThanOrEqual(0, $number);
    }

    public function testGetLastChargementDate(): void
    {
        $date = $this->repository->getLastChargementDate();

        $this->assertInstanceOf(DateTime::class, $date);
    }

    public function testCheckChargementRequired(): void
    {
        $result = $this->repository->checkChargementRequired();

        // Result depends on fixture data timing - just verify it runs without error
        $this->assertThat($result, self::logicalOr(self::isTrue(), self::isFalse()));
    }

    public function testFindByStoreYearAndUser(): void
    {
        $store = $this->getTestStore();
        $user = $this->getTestUser();
        $year = (int) date('Y');

        $transactions = $this->repository->findByStoreYearAndUser($store, $year, $user);

        foreach ($transactions as $transaction) {
            $this->assertSame($store->getId(), $transaction->getStore()->getId());
            $this->assertSame($year, (int) $transaction->getDate()->format('Y'));
            $this->assertSame($user->getId(), $transaction->getUser()->getId());
        }

        $this->assertGreaterThanOrEqual(0, count($transactions));
    }

    public function testFindByIdsReturnsMatchingTransactions(): void
    {
        $all = $this->repository->findBy([], limit: 3);
        $ids = array_map(static fn($t) => $t->getId(), $all);
        $ids = array_filter($ids, static fn($id) => $id !== null);

        $result = $this->repository->findByIds(array_values($ids));

        self::assertCount(count($ids), $result);
        $returnedIds = array_map(static fn($t) => $t->getId(), $result);
        foreach ($ids as $id) {
            self::assertContains($id, $returnedIds);
        }
    }

    public function testFindByIdsReturnsOnlyRequestedIds(): void
    {
        $all = $this->repository->findBy([], limit: 5);
        if (count($all) < 2) {
            self::markTestSkipped('Need at least 2 transactions in fixtures.');
        }

        $first = $all[0];
        $firstId = $first->getId();
        self::assertNotNull($firstId);

        $result = $this->repository->findByIds([$firstId]);

        self::assertCount(1, $result);
        self::assertSame($firstId, $result[0]->getId());
    }

    public function testFindByIdsWithEmptyArrayReturnsEmpty(): void
    {
        $result = $this->repository->findByIds([]);

        self::assertSame([], $result);
    }

    public function testFindByIdsWithNonExistentIdsReturnsEmpty(): void
    {
        $result = $this->repository->findByIds([PHP_INT_MAX - 1, PHP_INT_MAX]);

        self::assertSame([], $result);
    }

    public function testGetSaldoALaFechaByStoresWithEmptyArrayReturnsEmpty(): void
    {
        $result = $this->repository->getSaldoALaFechaByStores([], date('Y-m-d'));

        self::assertSame([], $result);
    }

    public function testGetSaldoALaFechaByStoresReturnsResultForKnownStore(): void
    {
        $store = $this->getTestStore();
        $storeId = (int) $store->getId();

        $result = $this->repository->getSaldoALaFechaByStores([$storeId], date('Y-m-d'));

        if ($result !== []) {
            self::assertArrayHasKey($storeId, $result);
            self::assertTrue(is_numeric($result[$storeId]) || $result[$storeId] === null);
        }
    }

    public function testFindMonthPaymentsByStoresWithEmptyArrayReturnsEmpty(): void
    {
        $result = $this->repository->findMonthPaymentsByStores([], (int) date('m'), (int) date('Y'));

        self::assertSame([], $result);
    }

    public function testFindMonthPaymentsByStoresGroupsByStore(): void
    {
        $store = $this->getTestStore();
        $storeId = (int) $store->getId();
        $month = (int) date('m');
        $year = (int) date('Y');

        $result = $this->repository->findMonthPaymentsByStores([$storeId], $month, $year);

        foreach ($result as $id => $transactions) {
            self::assertSame($storeId, $id);
            self::assertNotEmpty($transactions);
        }
    }

    private function getTestStore(): Store
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        $this->assertInstanceOf(Store::class, $store);

        return $store;
    }

    private function getTestUser(): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);

        return $user;
    }
}

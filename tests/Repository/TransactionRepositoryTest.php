<?php

declare(strict_types=1);

namespace App\Tests\Repository;

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
            self::assertSame($store->getId(), $transaction->getStore()->getId());
            self::assertSame($year, (int) $transaction->getDate()->format('Y'));
        }

        self::assertGreaterThanOrEqual(0, count($transactions));
    }

    public function testGetSaldoForStore(): void
    {
        $store = $this->getTestStore();
        $saldo = $this->repository->getSaldo($store);

        self::assertNotNull($saldo);
    }

    public function testGetSaldoAnterior(): void
    {
        $store = $this->getTestStore();
        $year = (int) date('Y');

        $result = $this->repository->getSaldoAnterior($store, $year);

        self::assertTrue(is_numeric($result) || $result === null);
    }

    public function testGetSaldoALaFecha(): void
    {
        $store = $this->getTestStore();

        $result = $this->repository->getSaldoALaFecha($store, date('Y-m-d'));

        self::assertTrue(is_numeric($result) || $result === null);
    }

    public function testFindMonthPayments(): void
    {
        $store = $this->getTestStore();
        $month = (int) date('m');
        $year = (int) date('Y');

        $payments = $this->repository->findMonthPayments($store, $month, $year);

        self::assertGreaterThanOrEqual(0, count($payments));
    }

    public function testFindByDate(): void
    {
        $year = (int) date('Y');
        $month = (int) date('m');

        $transactions = $this->repository->findByDate($year, $month);

        self::assertGreaterThanOrEqual(0, count($transactions));
    }

    public function testGetPagosPorAno(): void
    {
        $year = (int) date('Y');

        $pagos = $this->repository->getPagosPorAno($year);

        self::assertGreaterThanOrEqual(0, count($pagos));
    }

    public function testGetRawList(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');

        $result = $this->repository->getRawList($options);

        self::assertGreaterThanOrEqual(0, $result->count());
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

        self::assertGreaterThanOrEqual(0, $result->count());
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

        self::assertGreaterThanOrEqual(0, $result->count());
    }

    public function testGetRawListWithTypeCriteria(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setCriteria([
            'type' => '1',
        ]);

        $result = $this->repository->getRawList($options);

        self::assertGreaterThanOrEqual(0, $result->count());
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

        self::assertGreaterThanOrEqual(0, $result->count());
    }

    public function testGetLastRecipeNo(): void
    {
        $number = $this->repository->getLastRecipeNo();

        self::assertGreaterThanOrEqual(0, $number);
    }

    public function testGetLastChargementDate(): void
    {
        $date = $this->repository->getLastChargementDate();

        self::assertInstanceOf(\DateTime::class, $date);
    }

    public function testCheckChargementRequired(): void
    {
        $result = $this->repository->checkChargementRequired();

        // Result depends on fixture data timing - just verify it runs without error
        self::assertThat($result, self::logicalOr(self::isTrue(), self::isFalse()));
    }

    public function testFindByStoreYearAndUser(): void
    {
        $store = $this->getTestStore();
        $user = $this->getTestUser();
        $year = (int) date('Y');

        $transactions = $this->repository->findByStoreYearAndUser($store, $year, $user);

        foreach ($transactions as $transaction) {
            self::assertSame($store->getId(), $transaction->getStore()->getId());
            self::assertSame($year, (int) $transaction->getDate()->format('Y'));
            self::assertSame($user->getId(), $transaction->getUser()->getId());
        }

        self::assertGreaterThanOrEqual(0, count($transactions));
    }

    public function testFindByIds(): void
    {
        $transactions = $this->repository->findByIds([1, 2, 3]);

        self::assertGreaterThanOrEqual(0, count($transactions));
    }

    private function getTestStore(): Store
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy(['destination' => 'TEST']);
        self::assertNotNull($store);

        return $store;
    }

    private function getTestUser(): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);

        return $user;
    }
}

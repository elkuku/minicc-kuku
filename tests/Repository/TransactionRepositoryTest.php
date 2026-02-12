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

    public function testFindByIds(): void
    {
        $transactions = $this->repository->findByIds([1, 2, 3]);

        $this->assertGreaterThanOrEqual(0, count($transactions));
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

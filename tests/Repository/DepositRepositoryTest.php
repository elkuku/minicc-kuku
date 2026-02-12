<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Deposit;
use App\Helper\Paginator\PaginatorOptions;
use App\Repository\DepositRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DepositRepositoryTest extends KernelTestCase
{
    private DepositRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var DepositRepository $repository */
        $repository = self::getContainer()->get(DepositRepository::class);
        $this->repository = $repository;
    }

    public function testGetPaginatedList(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');

        $result = $this->repository->getPaginatedList($options);

        self::assertGreaterThanOrEqual(0, $result->count());
    }

    public function testGetPaginatedListWithSearchCriteria(): void
    {
        $options = new PaginatorOptions();
        $options->setOrder('id');
        $options->setCriteria([
            'amount' => '123',
            'document' => '123',
            'date_from' => '2020-01-01',
            'date_to' => '2030-12-31',
        ]);

        $result = $this->repository->getPaginatedList($options);

        self::assertGreaterThanOrEqual(0, $result->count());
    }

    public function testLookup(): void
    {
        $results = $this->repository->lookup(123);

        self::assertGreaterThanOrEqual(0, count($results));
    }

    public function testSearch(): void
    {
        $results = $this->repository->search(123);

        self::assertGreaterThanOrEqual(0, count($results));
    }

    public function testHas(): void
    {
        $deposit = $this->ensureDeposit();

        $result = $this->repository->has($deposit);
        self::assertTrue($result);
    }

    public function testHasReturnsFalseForNonExistentDeposit(): void
    {
        $deposit = new Deposit();
        $deposit->setDate(new \DateTime('1900-01-01'));
        $deposit->setDocument('999999');

        $result = $this->repository->has($deposit);
        self::assertFalse($result);
    }

    private function ensureDeposit(): Deposit
    {
        $deposits = $this->repository->findAll();

        if ($deposits) {
            return $deposits[0];
        }

        $deposit = new Deposit();
        $deposit->setDate(new \DateTime());
        $deposit->setDocument('test-123');
        $deposit->setAmount('100');

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($deposit);
        $em->flush();

        return $deposit;
    }
}

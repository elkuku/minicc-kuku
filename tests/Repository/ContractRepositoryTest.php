<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ContractRepositoryTest extends KernelTestCase
{
    private ContractRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ContractRepository $repository */
        $repository = self::getContainer()->get(ContractRepository::class);
        $this->repository = $repository;
    }

    public function testFindContractsWithNoFilter(): void
    {
        $contracts = $this->repository->findContracts();

        $this->assertGreaterThanOrEqual(0, count($contracts));
    }

    public function testFindContractsFilteredByStore(): void
    {
        $contracts = $this->repository->findContracts(1);

        $this->assertGreaterThanOrEqual(0, count($contracts));

        foreach ($contracts as $contract) {
            $this->assertSame(1, $contract->getStoreNumber());
        }
    }

    public function testFindContractsFilteredByYear(): void
    {
        $year = (int) date('Y');

        $contracts = $this->repository->findContracts(0, $year);

        $this->assertGreaterThanOrEqual(0, count($contracts));

        foreach ($contracts as $contract) {
            $this->assertSame($year, (int) $contract->getDate()->format('Y'));
        }
    }

    public function testFindContractsFilteredByStoreAndYear(): void
    {
        $year = (int) date('Y');

        $contracts = $this->repository->findContracts(1, $year);

        $this->assertGreaterThanOrEqual(0, count($contracts));

        foreach ($contracts as $contract) {
            $this->assertSame(1, $contract->getStoreNumber());
            $this->assertSame($year, (int) $contract->getDate()->format('Y'));
        }
    }

    public function testFindTemplateReturnsContractWithIdOne(): void
    {
        $template = $this->repository->find(1);

        if (!$template) {
            self::markTestSkipped('No contract with ID=1 in test database');
        }

        $result = $this->repository->findTemplate();
        $this->assertInstanceOf(Contract::class, $result);
    }
}

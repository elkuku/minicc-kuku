<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Contract;
use App\Entity\Store;
use App\Service\ContractFactory;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class ContractFactoryTest extends TestCase
{
    private ContractFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ContractFactory();
    }

    public function testCreateFromStoreMapsAllStoreFields(): void
    {
        $store = $this->makeStore();

        $contract = $this->factory->createFromStore($store);

        self::assertSame(42, $contract->getStoreNumber());
        self::assertSame('Shop A', $contract->getDestination());
        self::assertSame(500.0, $contract->getValAlq());
        self::assertSame(2, $contract->getCntLanfort());
        self::assertSame(3, $contract->getCntLlaves());
        self::assertSame(1, $contract->getCntMedAgua());
        self::assertSame(1, $contract->getCntMedElec());
        self::assertSame(4, $contract->getCntNeon());
        self::assertSame(2, $contract->getCntSwitch());
        self::assertSame(6, $contract->getCntToma());
        self::assertSame(3, $contract->getCntVentana());
        self::assertSame('MED-E-01', $contract->getMedElectrico());
        self::assertSame('MED-A-01', $contract->getMedAgua());
    }

    public function testCreateFromStoreWithTemplateAppliesText(): void
    {
        $store = $this->makeStore();
        $template = new Contract();
        $template->setText('Template body text');

        $contract = $this->factory->createFromStore($store, $template);

        self::assertSame('Template body text', $contract->getText());
    }

    public function testCreateFromStoreWithoutTemplateHasEmptyText(): void
    {
        $contract = $this->factory->createFromStore($this->makeStore());

        self::assertSame('', $contract->getText());
    }

    public function testCreateFromStoreReturnsNewContractEachTime(): void
    {
        $store = $this->makeStore();

        $a = $this->factory->createFromStore($store);
        $b = $this->factory->createFromStore($store);

        self::assertNotSame($a, $b);
    }

    private function makeStore(): Store
    {
        $store = new Store();
        $store->setDestination('Shop A')
            ->setValAlq(500.0)
            ->setCntLanfort(2)
            ->setCntLlaves(3)
            ->setCntMedAgua(1)
            ->setCntMedElec(1)
            ->setCntNeon(4)
            ->setCntSwitch(2)
            ->setCntToma(6)
            ->setCntVentana(3)
            ->setMedElectrico('MED-E-01')
            ->setMedAgua('MED-A-01');

        $reflection = new ReflectionProperty(Store::class, 'id');
        $reflection->setValue($store, 42);

        return $store;
    }
}

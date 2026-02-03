<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Contract;
use App\Entity\Store;
use App\Type\Gender;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class ContractTest extends TestCase
{
    public function testSetValuesFromStoreCopiesAllProperties(): void
    {
        $store = new Store();
        $this->setStoreId($store, 42);
        $store->setDestination('Tienda Centro');
        $store->setValAlq(500.50);
        $store->setCntLanfort(3);
        $store->setCntLlaves(2);
        $store->setCntMedAgua(1);
        $store->setCntMedElec(1);
        $store->setCntNeon(4);
        $store->setCntSwitch(5);
        $store->setCntToma(6);
        $store->setCntVentana(2);
        $store->setMedElectrico('ELEC-001');
        $store->setMedAgua('AGUA-001');

        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setValuesFromStore($store);

        self::assertSame($contract, $result);
        self::assertSame(42, $contract->getStoreNumber());
        self::assertSame('Tienda Centro', $contract->getDestination());
        self::assertSame(500.50, $contract->getValAlq());
        self::assertSame(3, $contract->getCntLanfort());
        self::assertSame(2, $contract->getCntLlaves());
        self::assertSame(1, $contract->getCntMedAgua());
        self::assertSame(1, $contract->getCntMedElec());
        self::assertSame(4, $contract->getCntNeon());
        self::assertSame(5, $contract->getCntSwitch());
        self::assertSame(6, $contract->getCntToma());
        self::assertSame(2, $contract->getCntVentana());
        self::assertSame('ELEC-001', $contract->getMedElectrico());
        self::assertSame('AGUA-001', $contract->getMedAgua());
    }

    public function testSetValuesFromStoreWithDefaultValues(): void
    {
        $store = new Store();
        $this->setStoreId($store, 1);

        $contract = new Contract();
        $contract->setGender(Gender::female);
        $contract->setValuesFromStore($store);

        self::assertSame(1, $contract->getStoreNumber());
        self::assertSame('', $contract->getDestination());
        self::assertSame(0.0, $contract->getValAlq());
        self::assertSame(0, $contract->getCntLanfort());
        self::assertSame(0, $contract->getCntLlaves());
        self::assertSame(0, $contract->getCntMedAgua());
        self::assertSame(0, $contract->getCntMedElec());
        self::assertSame(0, $contract->getCntNeon());
        self::assertSame(0, $contract->getCntSwitch());
        self::assertSame(0, $contract->getCntToma());
        self::assertSame(0, $contract->getCntVentana());
        self::assertSame('', $contract->getMedElectrico());
        self::assertSame('', $contract->getMedAgua());
    }

    public function testConstructorSetsDefaultDate(): void
    {
        $before = new DateTime();
        $contract = new Contract();
        $after = new DateTime();

        $date = $contract->getDate();

        self::assertGreaterThanOrEqual($before, $date);
        self::assertLessThanOrEqual($after, $date);
    }

    public function testGetIdReturnsNullForNewEntity(): void
    {
        $contract = new Contract();

        self::assertNull($contract->getId());
    }

    public function testInqNombreapellido(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setInqNombreapellido('Juan Pérez');

        self::assertSame($contract, $result);
        self::assertSame('Juan Pérez', $contract->getInqNombreapellido());
    }

    public function testInqCi(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        self::assertSame('000000000-0', $contract->getInqCi());

        $result = $contract->setInqCi('123456789-0');

        self::assertSame($contract, $result);
        self::assertSame('123456789-0', $contract->getInqCi());
    }

    public function testDestination(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setDestination('Local Comercial');

        self::assertSame($contract, $result);
        self::assertSame('Local Comercial', $contract->getDestination());
    }

    public function testValAlq(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setValAlq(750.25);

        self::assertSame($contract, $result);
        self::assertSame(750.25, $contract->getValAlq());
    }

    public function testValGarantia(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setValGarantia(1500.00);

        self::assertSame($contract, $result);
        self::assertSame(1500.00, $contract->getValGarantia());
    }

    public function testDate(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $date = new DateTime('2024-06-15');

        $result = $contract->setDate($date);

        self::assertSame($contract, $result);
        self::assertSame($date, $contract->getDate());
    }

    public function testGender(): void
    {
        $contract = new Contract();

        $result = $contract->setGender(Gender::female);

        self::assertSame($contract, $result);
        self::assertSame(Gender::female, $contract->getGender());
    }

    public function testStoreNumber(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setStoreNumber(123);

        self::assertSame($contract, $result);
        self::assertSame(123, $contract->getStoreNumber());
    }

    public function testText(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        self::assertSame('', $contract->getText());

        $result = $contract->setText('Contract text content here');

        self::assertSame($contract, $result);
        self::assertSame('Contract text content here', $contract->getText());
    }

    public function testAllCountFields(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setCntLanfort(10);
        self::assertSame($contract, $result);
        self::assertSame(10, $contract->getCntLanfort());

        $result = $contract->setCntNeon(5);
        self::assertSame($contract, $result);
        self::assertSame(5, $contract->getCntNeon());

        $result = $contract->setCntSwitch(8);
        self::assertSame($contract, $result);
        self::assertSame(8, $contract->getCntSwitch());

        $result = $contract->setCntToma(12);
        self::assertSame($contract, $result);
        self::assertSame(12, $contract->getCntToma());

        $result = $contract->setCntVentana(3);
        self::assertSame($contract, $result);
        self::assertSame(3, $contract->getCntVentana());

        $result = $contract->setCntLlaves(4);
        self::assertSame($contract, $result);
        self::assertSame(4, $contract->getCntLlaves());

        $result = $contract->setCntMedAgua(2);
        self::assertSame($contract, $result);
        self::assertSame(2, $contract->getCntMedAgua());

        $result = $contract->setCntMedElec(2);
        self::assertSame($contract, $result);
        self::assertSame(2, $contract->getCntMedElec());
    }

    public function testMeterFields(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        self::assertSame('', $contract->getMedElectrico());
        self::assertSame('', $contract->getMedAgua());

        $result = $contract->setMedElectrico('ELEC-2024-001');
        self::assertSame($contract, $result);
        self::assertSame('ELEC-2024-001', $contract->getMedElectrico());

        $result = $contract->setMedAgua('AGUA-2024-001');
        self::assertSame($contract, $result);
        self::assertSame('AGUA-2024-001', $contract->getMedAgua());
    }

    private function setStoreId(Store $store, int $id): void
    {
        $reflection = new ReflectionProperty(Store::class, 'id');
        $reflection->setValue($store, $id);
    }
}

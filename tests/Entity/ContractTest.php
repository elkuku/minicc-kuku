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

        $this->assertSame($contract, $result);
        $this->assertSame(42, $contract->getStoreNumber());
        $this->assertSame('Tienda Centro', $contract->getDestination());
        $this->assertEqualsWithDelta(500.50, $contract->getValAlq(), PHP_FLOAT_EPSILON);
        $this->assertSame(3, $contract->getCntLanfort());
        $this->assertSame(2, $contract->getCntLlaves());
        $this->assertSame(1, $contract->getCntMedAgua());
        $this->assertSame(1, $contract->getCntMedElec());
        $this->assertSame(4, $contract->getCntNeon());
        $this->assertSame(5, $contract->getCntSwitch());
        $this->assertSame(6, $contract->getCntToma());
        $this->assertSame(2, $contract->getCntVentana());
        $this->assertSame('ELEC-001', $contract->getMedElectrico());
        $this->assertSame('AGUA-001', $contract->getMedAgua());
    }

    public function testSetValuesFromStoreWithDefaultValues(): void
    {
        $store = new Store();
        $this->setStoreId($store, 1);

        $contract = new Contract();
        $contract->setGender(Gender::female);
        $contract->setValuesFromStore($store);

        $this->assertSame(1, $contract->getStoreNumber());
        $this->assertSame('', $contract->getDestination());
        $this->assertEqualsWithDelta(0.0, $contract->getValAlq(), PHP_FLOAT_EPSILON);
        $this->assertSame(0, $contract->getCntLanfort());
        $this->assertSame(0, $contract->getCntLlaves());
        $this->assertSame(0, $contract->getCntMedAgua());
        $this->assertSame(0, $contract->getCntMedElec());
        $this->assertSame(0, $contract->getCntNeon());
        $this->assertSame(0, $contract->getCntSwitch());
        $this->assertSame(0, $contract->getCntToma());
        $this->assertSame(0, $contract->getCntVentana());
        $this->assertSame('', $contract->getMedElectrico());
        $this->assertSame('', $contract->getMedAgua());
    }

    public function testConstructorSetsDefaultDate(): void
    {
        $before = new DateTime();
        $contract = new Contract();
        $after = new DateTime();

        $date = $contract->getDate();

        $this->assertGreaterThanOrEqual($before, $date);
        $this->assertLessThanOrEqual($after, $date);
    }

    public function testGetIdReturnsNullForNewEntity(): void
    {
        $contract = new Contract();

        $this->assertNull($contract->getId());
    }

    public function testInqNombreapellido(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setInqNombreapellido('Juan Pérez');

        $this->assertSame($contract, $result);
        $this->assertSame('Juan Pérez', $contract->getInqNombreapellido());
    }

    public function testInqCi(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $this->assertSame('000000000-0', $contract->getInqCi());

        $result = $contract->setInqCi('123456789-0');

        $this->assertSame($contract, $result);
        $this->assertSame('123456789-0', $contract->getInqCi());
    }

    public function testDestination(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setDestination('Local Comercial');

        $this->assertSame($contract, $result);
        $this->assertSame('Local Comercial', $contract->getDestination());
    }

    public function testValAlq(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setValAlq(750.25);

        $this->assertSame($contract, $result);
        $this->assertEqualsWithDelta(750.25, $contract->getValAlq(), PHP_FLOAT_EPSILON);
    }

    public function testValGarantia(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setValGarantia(1500.00);

        $this->assertSame($contract, $result);
        $this->assertEqualsWithDelta(1500.00, $contract->getValGarantia(), PHP_FLOAT_EPSILON);
    }

    public function testDate(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $date = new DateTime('2024-06-15');

        $result = $contract->setDate($date);

        $this->assertSame($contract, $result);
        $this->assertSame($date, $contract->getDate());
    }

    public function testGender(): void
    {
        $contract = new Contract();

        $result = $contract->setGender(Gender::female);

        $this->assertSame($contract, $result);
        $this->assertSame(Gender::female, $contract->getGender());
    }

    public function testStoreNumber(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setStoreNumber(123);

        $this->assertSame($contract, $result);
        $this->assertSame(123, $contract->getStoreNumber());
    }

    public function testText(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $this->assertSame('', $contract->getText());

        $result = $contract->setText('Contract text content here');

        $this->assertSame($contract, $result);
        $this->assertSame('Contract text content here', $contract->getText());
    }

    public function testAllCountFields(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $result = $contract->setCntLanfort(10);
        $this->assertSame($contract, $result);
        $this->assertSame(10, $contract->getCntLanfort());

        $result = $contract->setCntNeon(5);
        $this->assertSame($contract, $result);
        $this->assertSame(5, $contract->getCntNeon());

        $result = $contract->setCntSwitch(8);
        $this->assertSame($contract, $result);
        $this->assertSame(8, $contract->getCntSwitch());

        $result = $contract->setCntToma(12);
        $this->assertSame($contract, $result);
        $this->assertSame(12, $contract->getCntToma());

        $result = $contract->setCntVentana(3);
        $this->assertSame($contract, $result);
        $this->assertSame(3, $contract->getCntVentana());

        $result = $contract->setCntLlaves(4);
        $this->assertSame($contract, $result);
        $this->assertSame(4, $contract->getCntLlaves());

        $result = $contract->setCntMedAgua(2);
        $this->assertSame($contract, $result);
        $this->assertSame(2, $contract->getCntMedAgua());

        $result = $contract->setCntMedElec(2);
        $this->assertSame($contract, $result);
        $this->assertSame(2, $contract->getCntMedElec());
    }

    public function testMeterFields(): void
    {
        $contract = new Contract();
        $contract->setGender(Gender::male);

        $this->assertSame('', $contract->getMedElectrico());
        $this->assertSame('', $contract->getMedAgua());

        $result = $contract->setMedElectrico('ELEC-2024-001');
        $this->assertSame($contract, $result);
        $this->assertSame('ELEC-2024-001', $contract->getMedElectrico());

        $result = $contract->setMedAgua('AGUA-2024-001');
        $this->assertSame($contract, $result);
        $this->assertSame('AGUA-2024-001', $contract->getMedAgua());
    }

    private function setStoreId(Store $store, int $id): void
    {
        $reflection = new ReflectionProperty(Store::class, 'id');
        $reflection->setValue($store, $id);
    }
}

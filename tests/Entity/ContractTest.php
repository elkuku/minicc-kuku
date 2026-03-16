<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Contract;
use App\Type\Gender;
use DateTime;
use PHPUnit\Framework\TestCase;

final class ContractTest extends TestCase
{
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

}

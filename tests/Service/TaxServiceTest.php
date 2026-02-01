<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\TaxService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaxServiceTest extends TestCase
{
    public function testGetTaxValue(): void
    {
        $service = new TaxService(12);
        self::assertSame(12.0, $service->getTaxValue());
    }

    public function testGetTaxValueWithDecimal(): void
    {
        $service = new TaxService(12.5);
        self::assertSame(12.5, $service->getTaxValue());
    }

    #[DataProvider('addTaxProvider')]
    public function testAddTax(float $taxRate, float $baseValue, float $expected): void
    {
        $service = new TaxService($taxRate);
        self::assertSame($expected, $service->addTax($baseValue));
    }

    /**
     * @return array<string, array{float, float, float}>
     */
    public static function addTaxProvider(): array
    {
        return [
            '12% on 100' => [12, 100, 112.0],
            '12% on 0' => [12, 0, 0.0],
            '12.5% on 100' => [12.5, 100, 112.5],
            '0% on 100' => [0, 100, 100.0],
            '12% on 50.50' => [12, 50.50, 56.56],
        ];
    }

    #[DataProvider('getTaxFromTotalProvider')]
    public function testGetTaxFromTotal(float $taxRate, float $total, float $expected): void
    {
        $service = new TaxService($taxRate);
        self::assertSame($expected, $service->getTaxFromTotal($total));
    }

    /**
     * @return array<string, array{float, float, float}>
     */
    public static function getTaxFromTotalProvider(): array
    {
        return [
            '12% from 112' => [12, 112, 12.0],
            '12% from 0' => [12, 0, 0.0],
            '12.5% from 112.50' => [12.5, 112.50, 12.5],
            '0% from 100' => [0, 100, 0.0],
        ];
    }

    #[DataProvider('getBaseFromTotalProvider')]
    public function testGetBaseFromTotal(float $taxRate, float $total, float $expected): void
    {
        $service = new TaxService($taxRate);
        self::assertSame($expected, $service->getBaseFromTotal($total));
    }

    /**
     * @return array<string, array{float, float, float}>
     */
    public static function getBaseFromTotalProvider(): array
    {
        return [
            '12% from 112' => [12, 112, 100.0],
            '12% from 0' => [12, 0, 0.0],
            '12.5% from 112.50' => [12.5, 112.50, 100.0],
            '0% from 100' => [0, 100, 100.0],
        ];
    }

    public function testRoundTrip(): void
    {
        $service = new TaxService(12);
        $base = 100.0;

        $withTax = $service->addTax($base);
        $backToBase = $service->getBaseFromTotal($withTax);

        self::assertSame($base, $backToBase);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Entity\User;
use App\Service\TextFormatter;
use App\Twig\Extension\TwigExtension;
use App\Type\Gender;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TwigExtensionUnitTest extends TestCase
{
    private TwigExtension $extension;

    protected function setUp(): void
    {
        $textFormatter = new TextFormatter();
        $this->extension = new TwigExtension($textFormatter);
    }

    #[DataProvider('priceFilterProvider')]
    public function testPriceFilter(?float $number, string $expectedClass, string $expectedValue): void
    {
        $result = $this->extension->priceFilter($number);

        self::assertStringContainsString($expectedClass, $result);
        self::assertStringContainsString($expectedValue, $result);
    }

    /**
     * @return array<string, array{float|null, string, string}>
     */
    public static function priceFilterProvider(): array
    {
        return [
            'positive number' => [100.50, 'class="amount"', '100.50'],
            'negative number' => [-50.25, 'class="amount amount-red"', '-50.25'],
            'zero' => [0.0, 'class="amount"', '0'],
            'null' => [null, 'class="amount"', '0'],
            'large number' => [1234567.89, 'class="amount"', '1,234,567.89'],
            'small negative' => [-0.01, 'class="amount amount-red"', '-0.01'],
        ];
    }

    public function testPriceFilterCustomDecimals(): void
    {
        $result = $this->extension->priceFilter(100.12345, 4);

        self::assertStringContainsString('100.1235', $result);
    }

    public function testPriceFilterCustomSeparators(): void
    {
        $result = $this->extension->priceFilter(1234.56, 2, ',', '.');

        self::assertStringContainsString('1.234,56', $result);
    }

    public function testIntlDateWithDateTime(): void
    {
        $date = new DateTime('2024-03-15');

        $result = $this->extension->intlDate($date);

        self::assertStringContainsString('15', $result);
        self::assertStringContainsString('marzo', $result);
        self::assertStringContainsString('2024', $result);
    }

    public function testIntlDateWithString(): void
    {
        $result = $this->extension->intlDate('2024-06-20');

        self::assertStringContainsString('20', $result);
        self::assertStringContainsString('junio', $result);
        self::assertStringContainsString('2024', $result);
    }

    public function testIntlDateWithCustomFormat(): void
    {
        $date = new DateTime('2024-12-25');

        $result = $this->extension->intlDate($date, 'MMMM YYYY');

        self::assertStringContainsString('diciembre', $result);
        self::assertStringContainsString('2024', $result);
    }

    public function testIntlDateWithGermanLocale(): void
    {
        $date = new DateTime('2024-03-15');

        $result = $this->extension->intlDate($date, "d. MMMM YYYY", 'de_DE');

        self::assertStringContainsString('März', $result);
    }

    public function testIntlDateWithInvalidStringReturnsOriginal(): void
    {
        $invalidDate = 'not-a-date';

        $result = $this->extension->intlDate($invalidDate);

        self::assertSame($invalidDate, $result);
    }

    public function testFormatRUC(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);
        $user->setInqRuc('1234567890001');

        $result = $this->extension->formatRUC($user);

        self::assertSame('123 456 789 0 001', $result);
    }

    #[DataProvider('monthProvider')]
    public function testIntlDateAllMonths(string $date, string $expectedMonth): void
    {
        $result = $this->extension->intlDate($date, 'MMMM');

        self::assertStringContainsString($expectedMonth, $result);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function monthProvider(): array
    {
        return [
            'january' => ['2024-01-15', 'enero'],
            'february' => ['2024-02-15', 'febrero'],
            'march' => ['2024-03-15', 'marzo'],
            'april' => ['2024-04-15', 'abril'],
            'may' => ['2024-05-15', 'mayo'],
            'june' => ['2024-06-15', 'junio'],
            'july' => ['2024-07-15', 'julio'],
            'august' => ['2024-08-15', 'agosto'],
            'september' => ['2024-09-15', 'septiembre'],
            'october' => ['2024-10-15', 'octubre'],
            'november' => ['2024-11-15', 'noviembre'],
            'december' => ['2024-12-15', 'diciembre'],
        ];
    }
}

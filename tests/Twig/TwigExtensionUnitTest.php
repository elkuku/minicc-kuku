<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use Iterator;
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

        $this->assertStringContainsString($expectedClass, $result);
        $this->assertStringContainsString($expectedValue, $result);
    }

    /**
     * @return Iterator<string, array{(float | null), string, string}>
     */
    public static function priceFilterProvider(): Iterator
    {
        yield 'positive number' => [100.50, 'class="amount"', '100.50'];
        yield 'negative number' => [-50.25, 'class="amount amount-red"', '-50.25'];
        yield 'zero' => [0.0, 'class="amount"', '0'];
        yield 'null' => [null, 'class="amount"', '0'];
        yield 'large number' => [1234567.89, 'class="amount"', '1,234,567.89'];
        yield 'small negative' => [-0.01, 'class="amount amount-red"', '-0.01'];
    }

    public function testPriceFilterCustomDecimals(): void
    {
        $result = $this->extension->priceFilter(100.12345, 4);

        $this->assertStringContainsString('100.1235', $result);
    }

    public function testPriceFilterCustomSeparators(): void
    {
        $result = $this->extension->priceFilter(1234.56, 2, ',', '.');

        $this->assertStringContainsString('1.234,56', $result);
    }

    public function testIntlDateWithDateTime(): void
    {
        $date = new DateTime('2024-03-15');

        $result = $this->extension->intlDate($date);

        $this->assertStringContainsString('15', $result);
        $this->assertStringContainsString('marzo', $result);
        $this->assertStringContainsString('2024', $result);
    }

    public function testIntlDateWithString(): void
    {
        $result = $this->extension->intlDate('2024-06-20');

        $this->assertStringContainsString('20', $result);
        $this->assertStringContainsString('junio', $result);
        $this->assertStringContainsString('2024', $result);
    }

    public function testIntlDateWithCustomFormat(): void
    {
        $date = new DateTime('2024-12-25');

        $result = $this->extension->intlDate($date, 'MMMM YYYY');

        $this->assertStringContainsString('diciembre', $result);
        $this->assertStringContainsString('2024', $result);
    }

    public function testIntlDateWithGermanLocale(): void
    {
        $date = new DateTime('2024-03-15');

        $result = $this->extension->intlDate($date, "d. MMMM YYYY", 'de_DE');

        $this->assertStringContainsString('März', $result);
    }

    public function testIntlDateWithInvalidStringReturnsOriginal(): void
    {
        $invalidDate = 'not-a-date';

        $result = $this->extension->intlDate($invalidDate);

        $this->assertSame($invalidDate, $result);
    }

    public function testFormatRUC(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);
        $user->setInqRuc('1234567890001');

        $result = $this->extension->formatRUC($user);

        $this->assertSame('123 456 789 0 001', $result);
    }

    #[DataProvider('monthProvider')]
    public function testIntlDateAllMonths(string $date, string $expectedMonth): void
    {
        $result = $this->extension->intlDate($date, 'MMMM');

        $this->assertStringContainsString($expectedMonth, $result);
    }

    /**
     * @return Iterator<string, array{string, string}>
     */
    public static function monthProvider(): Iterator
    {
        yield 'january' => ['2024-01-15', 'enero'];
        yield 'february' => ['2024-02-15', 'febrero'];
        yield 'march' => ['2024-03-15', 'marzo'];
        yield 'april' => ['2024-04-15', 'abril'];
        yield 'may' => ['2024-05-15', 'mayo'];
        yield 'june' => ['2024-06-15', 'junio'];
        yield 'july' => ['2024-07-15', 'julio'];
        yield 'august' => ['2024-08-15', 'agosto'];
        yield 'september' => ['2024-09-15', 'septiembre'];
        yield 'october' => ['2024-10-15', 'octubre'];
        yield 'november' => ['2024-11-15', 'noviembre'];
        yield 'december' => ['2024-12-15', 'diciembre'];
    }
}

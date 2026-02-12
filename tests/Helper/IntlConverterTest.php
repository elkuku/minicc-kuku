<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use Iterator;
use App\Helper\IntlConverter;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IntlConverterTest extends TestCase
{
    public function testFormatDateWithDateTimeObject(): void
    {
        $date = new DateTime('2024-03-15');

        $result = IntlConverter::formatDate($date);

        $this->assertStringContainsString('15', $result);
        $this->assertStringContainsString('marzo', $result);
        $this->assertStringContainsString('2024', $result);
    }

    public function testFormatDateWithStringDate(): void
    {
        $result = IntlConverter::formatDate('2024-03-15');

        $this->assertStringContainsString('15', $result);
        $this->assertStringContainsString('marzo', $result);
        $this->assertStringContainsString('2024', $result);
    }

    public function testFormatDateWithCustomFormat(): void
    {
        $date = new DateTime('2024-12-25');

        $result = IntlConverter::formatDate($date, 'MMMM YYYY');

        $this->assertStringContainsString('diciembre', $result);
        $this->assertStringContainsString('2024', $result);
    }

    public function testFormatDateWithGermanLocale(): void
    {
        $date = new DateTime('2024-03-15');

        $result = IntlConverter::formatDate($date, "d. MMMM YYYY", 'de_DE');

        $this->assertStringContainsString('15', $result);
        $this->assertStringContainsString('März', $result);
        $this->assertStringContainsString('2024', $result);
    }

    public function testFormatDateWithEnglishLocale(): void
    {
        $date = new DateTime('2024-03-15');

        $result = IntlConverter::formatDate($date, 'MMMM d, YYYY', 'en_US');

        $this->assertStringContainsString('15', $result);
        $this->assertStringContainsString('March', $result);
        $this->assertStringContainsString('2024', $result);
    }

    #[DataProvider('dateFormattingProvider')]
    public function testFormatDateVariousFormats(string $dateString, string $format, string $expectedContains): void
    {
        $date = new DateTime($dateString);

        $result = IntlConverter::formatDate($date, $format, 'es_ES');

        $this->assertStringContainsString($expectedContains, $result);
    }

    /**
     * @return Iterator<string, array{string, string, string}>
     */
    public static function dateFormattingProvider(): Iterator
    {
        yield 'january' => ['2024-01-01', 'MMMM', 'enero'];
        yield 'february' => ['2024-02-01', 'MMMM', 'febrero'];
        yield 'march' => ['2024-03-01', 'MMMM', 'marzo'];
        yield 'april' => ['2024-04-01', 'MMMM', 'abril'];
        yield 'may' => ['2024-05-01', 'MMMM', 'mayo'];
        yield 'june' => ['2024-06-01', 'MMMM', 'junio'];
        yield 'july' => ['2024-07-01', 'MMMM', 'julio'];
        yield 'august' => ['2024-08-01', 'MMMM', 'agosto'];
        yield 'september' => ['2024-09-01', 'MMMM', 'septiembre'];
        yield 'october' => ['2024-10-01', 'MMMM', 'octubre'];
        yield 'november' => ['2024-11-01', 'MMMM', 'noviembre'];
        yield 'december' => ['2024-12-01', 'MMMM', 'diciembre'];
        yield 'year only' => ['2024-06-15', 'YYYY', '2024'];
        yield 'day only' => ['2024-06-15', 'd', '15'];
    }

    public function testFormatDateDefaultFormat(): void
    {
        $date = new DateTime('2024-06-15');

        $result = IntlConverter::formatDate($date);

        $this->assertStringContainsString('15', $result);
        $this->assertStringContainsString('de', $result);
        $this->assertStringContainsString('junio', $result);
        $this->assertStringContainsString('2024', $result);
    }
}

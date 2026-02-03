<?php

declare(strict_types=1);

namespace App\Tests\Helper;

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

        self::assertStringContainsString('15', $result);
        self::assertStringContainsString('marzo', $result);
        self::assertStringContainsString('2024', $result);
    }

    public function testFormatDateWithStringDate(): void
    {
        $result = IntlConverter::formatDate('2024-03-15');

        self::assertStringContainsString('15', $result);
        self::assertStringContainsString('marzo', $result);
        self::assertStringContainsString('2024', $result);
    }

    public function testFormatDateWithCustomFormat(): void
    {
        $date = new DateTime('2024-12-25');

        $result = IntlConverter::formatDate($date, 'MMMM YYYY');

        self::assertStringContainsString('diciembre', $result);
        self::assertStringContainsString('2024', $result);
    }

    public function testFormatDateWithGermanLocale(): void
    {
        $date = new DateTime('2024-03-15');

        $result = IntlConverter::formatDate($date, "d. MMMM YYYY", 'de_DE');

        self::assertStringContainsString('15', $result);
        self::assertStringContainsString('März', $result);
        self::assertStringContainsString('2024', $result);
    }

    public function testFormatDateWithEnglishLocale(): void
    {
        $date = new DateTime('2024-03-15');

        $result = IntlConverter::formatDate($date, 'MMMM d, YYYY', 'en_US');

        self::assertStringContainsString('15', $result);
        self::assertStringContainsString('March', $result);
        self::assertStringContainsString('2024', $result);
    }

    #[DataProvider('dateFormattingProvider')]
    public function testFormatDateVariousFormats(string $dateString, string $format, string $expectedContains): void
    {
        $date = new DateTime($dateString);

        $result = IntlConverter::formatDate($date, $format, 'es_ES');

        self::assertStringContainsString($expectedContains, $result);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function dateFormattingProvider(): array
    {
        return [
            'january' => ['2024-01-01', 'MMMM', 'enero'],
            'february' => ['2024-02-01', 'MMMM', 'febrero'],
            'march' => ['2024-03-01', 'MMMM', 'marzo'],
            'april' => ['2024-04-01', 'MMMM', 'abril'],
            'may' => ['2024-05-01', 'MMMM', 'mayo'],
            'june' => ['2024-06-01', 'MMMM', 'junio'],
            'july' => ['2024-07-01', 'MMMM', 'julio'],
            'august' => ['2024-08-01', 'MMMM', 'agosto'],
            'september' => ['2024-09-01', 'MMMM', 'septiembre'],
            'october' => ['2024-10-01', 'MMMM', 'octubre'],
            'november' => ['2024-11-01', 'MMMM', 'noviembre'],
            'december' => ['2024-12-01', 'MMMM', 'diciembre'],
            'year only' => ['2024-06-15', 'YYYY', '2024'],
            'day only' => ['2024-06-15', 'd', '15'],
        ];
    }

    public function testFormatDateDefaultFormat(): void
    {
        $date = new DateTime('2024-06-15');

        $result = IntlConverter::formatDate($date);

        self::assertStringContainsString('15', $result);
        self::assertStringContainsString('de', $result);
        self::assertStringContainsString('junio', $result);
        self::assertStringContainsString('2024', $result);
    }
}

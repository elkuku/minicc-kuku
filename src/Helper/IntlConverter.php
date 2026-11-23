<?php

declare(strict_types=1);

namespace App\Helper;

use DateTime;
use DateTimeInterface;
use IntlDateFormatter;

/**
 * @see \App\Tests\Helper\IntlConverterTest
 */
class IntlConverter
{
    public static function formatDate(
        string|DateTimeInterface $date,
        string $format = "d 'de' MMMM YYYY",
        string $lang = 'es_ES',
    ): string {
        $formatter = new IntlDateFormatter(
            'ES_es',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE
        );

        $dateTime = \is_object($date) ? $date : new DateTime($date);

        return $formatter->formatObject($dateTime, $format, $lang);
    }
}

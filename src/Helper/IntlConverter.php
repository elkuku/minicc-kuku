<?php

declare(strict_types=1);

namespace App\Helper;

use DateTime;
use IntlDateFormatter;
use function is_object;

class IntlConverter
{
    public static function formatDate(
        string|DateTime $date,
        string $format = "d 'de' MMMM YYYY",
        string $lang = 'es_ES'
    ): string
    {
        $formatter = new IntlDateFormatter(
            'ES_es',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE
        );

        $dateTime = is_object($date) ? $date : new DateTime($date);

        return $formatter->formatObject($dateTime, $format, $lang);
    }
}

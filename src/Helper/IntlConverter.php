<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Helper;

use DateTime;
use IntlDateFormatter;
use function is_object;

class IntlConverter
{
    public function __construct(
        private string $defaultLocale,
        private string $defaultCurrency
    ) {
    }

    public static function formatDate(
        string|\DateTime $date,
        string $format = "d 'de' MMMM YYYY",
        string $lang = 'es_ES'
    ): string {
        $formatter = new IntlDateFormatter(
            'ES_es',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE
        );

        $dateTime = is_object($date) ? $date : new DateTime($date);

        return $formatter->formatObject($dateTime, $format, $lang);
    }

    public function toCurrencyWords(
        float $ammount,
        string $locale = null,
        string $currency = null
    ): void {
        $locale = $locale ?? $this->defaultLocale;
        $currency = $currency ?? $this->defaultCurrency;

        $a = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
        echo $a->formatCurrency(1_231_231.45, $currency) .PHP_EOL;
        echo $a->format(1_231_231.45) .PHP_EOL;

    }
}

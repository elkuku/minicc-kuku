<?php

namespace App\Twig\Extension;

use App\Entity\User;
use App\Twig\Runtime\AppExtensionRuntime;
use DateTime;
use Exception;
use IntlDateFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function count;
use function strlen;

class AppExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('price', $this->priceFilter(...)),
            new TwigFilter('conIva', [AppExtensionRuntime::class, 'getValueWithTax']),
            new TwigFilter('taxFromTotal', [AppExtensionRuntime::class, 'getTaxFromTotal']),
            new TwigFilter('invert', $this->invertFilter(...)),
            new TwigFilter('cast_to_array', $this->objectFilter(...)),
            new TwigFilter('short_name', $this->shortName(...)),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('intlDate', $this->intlDate(...)),
            new TwigFunction('formatRUC', $this->formatRUC(...)),
            new TwigFunction('getSHA', [AppExtensionRuntime::class, 'getSHA']),
            new TwigFunction('findSystemUsers', [AppExtensionRuntime::class, 'getSystemUsers']),
            new TwigFunction('getCurrentYear', $this->getCurrentYear(...)),
        ];
    }

    public function priceFilter(
        float|null $number,
        int $decimals = 2,
        string $decPoint = '.',
        string $thousandsSep = ','
    ): string {
        $price = $number ? number_format(
            $number,
            $decimals,
            $decPoint,
            $thousandsSep
        ) : 0;

        return sprintf(
            '<span class="%s">%s</span>',
            $price < 0 ? 'amount amount-red' : 'amount',
            $price
        );
    }

    /**
     * Invert a value.
     */
    public function invertFilter(int|float $value): int|float
    {
        return $value ? -$value : 0;
    }

    public function intlDate(
        string|DateTime $date,
        string          $format = "d 'de' MMMM YYYY",
        string          $lang = 'es_ES'
    ): string {
        $formatter = new IntlDateFormatter(
            'es_ES',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE
        );

        if ($date instanceof DateTime) {
            $dateTime = $date;
        } else {
            try {
                $dateTime = new DateTime($date);
            } catch (Exception) {
                return $date;
            }
        }

        return $formatter->formatObject($dateTime, $format, $lang);
    }

    /**
     * Convert object to array for Twig usage...
     *
     * @return array<string, mixed>
     */
    public function objectFilter(object $classObject): array
    {
        $array = (array) $classObject;
        $response = [];

        $className = $classObject::class;

        foreach ($array as $k => $v) {
            $response[trim(str_replace($className, '', $k))] = $v;
        }

        return $response;
    }

    /**
     * Shorten a (latin) name.
     */
    public function shortName(string $longName): string
    {
        // E.g. Juan José Perez Pillo
        $parts = explode(' ', $longName);

        if (2 === count($parts)) {
            // Juan Perez => Juan Perez
            return $longName;
        }

        if (3 === count($parts)) {
            // Juan José Perez => Juan Perez
            return $parts[0] . ' ' . $parts[2];
        }

        if (4 === count($parts)) {
            // Juan José Perez Pillo => Juan Perez
            return $parts[0] . ' ' . $parts[2];
        }

        return $longName;
    }

    /**
     * Cleanup a long number and make it readable.
     */
    public function formatRUC(User $user): string
    {
        $ruc = '?';

        if ($user->getInqRuc()) {
            $ruc = $user->getInqRuc();

            if (13 === strlen($ruc)) {
                $rucs = str_split($ruc, 10);

                $ruc = trim(chunk_split($rucs[0], 3, ' ')) . ' ' . $rucs[1];
            } else {
                $ruc = chunk_split($ruc, 3, ' ');
            }
        } elseif ($user->getInqCi()) {
            $ruc = str_replace('-', '', $user->getInqCi());
            $ruc = chunk_split($ruc, 3, ' ');
        }

        return trim($ruc);
    }

    public function getCurrentYear(): int
    {
        return (int) date('Y');
    }
}
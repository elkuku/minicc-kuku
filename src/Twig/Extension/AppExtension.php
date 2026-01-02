<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Override;
use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function count;

class AppExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    #[Override]
    public function getFilters(): array
    {
        return [
        //    new TwigFilter('price', $this->priceFilter(...)),
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
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getSHA', [AppExtensionRuntime::class, 'getSHA']),
            new TwigFunction('findSystemUsers', [AppExtensionRuntime::class, 'getSystemUsers']),
            new TwigFunction('getCurrentYear', $this->getCurrentYear(...)),
        ];
    }

    /**
     * Invert a value.
     */
    public function invertFilter(int|float $value): int|float
    {
        return $value ? -$value : 0;
    }



    /**
     * Convert object to array for Twig usage...
     *
     * @return array<string, mixed>
     */
    public function objectFilter(object $classObject): array
    {
        $array = (array)$classObject;
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



    public function getCurrentYear(): int
    {
        return (int)date('Y');
    }
}

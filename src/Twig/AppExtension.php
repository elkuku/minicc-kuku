<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Twig;

use App\Entity\User;
use App\Service\ShaFinder;
use App\Service\TaxService;
use DateTime;
use Exception;
use IntlDateFormatter;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function count;
use function strlen;

class AppExtension extends AbstractExtension
    implements ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * {@inheritdoc}
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('price', [$this, 'priceFilter']),
            new TwigFilter('conIva', [$this, 'conIvaFilter']),
            new TwigFilter('taxFromTotal', [$this, 'taxFromTotalFilter']),
            new TwigFilter('invert', [$this, 'invertFilter']),
            new TwigFilter('cast_to_array', [$this, 'objectFilter']),
            new TwigFilter('short_name', [$this, 'shortName']),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('intlDate', [$this, 'intlDate']),
            new TwigFunction('formatRUC', [$this, 'formatRUC']),
            new TwigFunction('getSHA', [$this, 'getSHA']),
        ];
    }

    /**
     * @return class-string[]
     */
    public static function getSubscribedServices(): array
    {
        return [
            ShaFinder::class,
            TaxService::class,
        ];
    }

    public function priceFilter(
        float|null $number,
        int $decimals = 2,
        string $decPoint = '.',
        string $thousandsSep = ','
    ): string {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);

        return sprintf(
            '<span class="%s">%s</span>',
            ($price < 0 ? 'amount amount-red' : 'amount'),
            $price
        );
    }

    /**
     * Invert a value
     */
    public function invertFilter(int $value): int
    {
        return -$value;
    }

    /**
     * @throws \Exception
     */
    public function intlDate(
        string|DateTime $date,
        string $format = "d 'de' MMMM YYYY",
        string $lang = 'es_ES'
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
        $array = (array)$classObject;
        $response = [];

        $className = $classObject::class;

        foreach ($array as $k => $v) {
            $response[trim(str_replace($className, '', $k))] = $v;
        }

        return $response;
    }

    /**
     * Shorten a (latin) name
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
            return $parts[0].' '.$parts[2];
        }

        if (4 === count($parts)) {
            // Juan José Perez Pillo => Juan Perez
            return $parts[0].' '.$parts[2];
        }

        return $longName;
    }

    /**
     * Add the tax value to a given amount.
     */
    public function conIvaFilter(float $value): float
    {
        return $this->container->get(TaxService::class)->getValueConTax($value);
    }

    /**
     * Add the tax value to a given amount.
     */
    public function taxFromTotalFilter(float $value): float
    {
        return $this->container->get(TaxService::class)
            ->getTaxFromTotal($value);
    }

    public function getSHA(): string
    {
        return $this->container->get(ShaFinder::class)->getSha();
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

                $ruc = trim(chunk_split($rucs[0], 3, ' ')).' '.$rucs[1];
            } else {
                $ruc = chunk_split($ruc, 3, ' ');
            }
        } elseif ($user->getInqCi()) {
            $ruc = str_replace('-', '', $user->getInqCi());
            $ruc = chunk_split($ruc, 3, ' ');
        }

        return trim($ruc);
    }
}

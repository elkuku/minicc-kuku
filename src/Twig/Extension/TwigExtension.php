<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\User;
use App\Service\TextFormatter;
use DateTime;
use Exception;
use IntlDateFormatter;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

class TwigExtension
{
    public function __construct(private readonly TextFormatter $textFormatter) {}

    #[AsTwigFilter('price')]
    public function priceFilter(
        float|null $number,
        int $decimals = 2,
        string $decPoint = '.',
        string $thousandsSep = ','
    ): string
    {
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
     * Cleanup a long number and make it readable.
     */
    #[AsTwigFunction('formatRUC')]
    public function formatRUC(User $user): string
    {
        return $this->textFormatter->formatRUC($user);
    }

    #[AsTwigFunction('intlDate')]
    public function intlDate(
        string|DateTime $date,
        string $format = "d 'de' MMMM YYYY",
        string $lang = 'es_ES'
    ): string
    {
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
}

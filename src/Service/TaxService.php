<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TaxService
{
    private readonly float $taxMultiplier;

    public function __construct(
        #[Autowire('%env(float:VALUE_IVA)%')]
        private readonly float $taxRate,
    ) {
        $this->taxMultiplier = 1 + $this->taxRate / 100;
    }

    public function getTaxValue(): float
    {
        return $this->taxRate;
    }

    /**
     * Calculate total with tax from base value.
     * Example: 100 with 12% tax = 112
     */
    public function addTax(float $baseValue): float
    {
        return round($baseValue * $this->taxMultiplier, 2);
    }

    /**
     * Extract tax amount from total value.
     * Example: 112 total with 12% tax = 12 tax
     */
    public function getTaxFromTotal(float $total): float
    {
        return round($total - $this->getBaseFromTotal($total), 2);
    }

    /**
     * Extract base value from total with tax.
     * Example: 112 total with 12% tax = 100 base
     */
    public function getBaseFromTotal(float $total): float
    {
        return round($total / $this->taxMultiplier, 2);
    }
}

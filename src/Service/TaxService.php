<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 25/05/17
 * Time: 16:45
 */

namespace App\Service;

/**
 * Class TaxService
 */
class TaxService
{
    /**
     * TaxService constructor.
     */
    public function __construct(
        /**
         * @var integer
         */
        private $taxValue
    ) {
    }

    public function getTaxValue(): int
    {
        return $this->taxValue;
    }

    /**
     * Add the tax value to a given amount.
     */
    public function getValueConTax(float $value): float
    {
        return round($value * (1 + $this->taxValue / 100), 2);
    }

    /**
     * Get the tax amount from total value.
     */
    public function getTaxFromTotal(float $total): float
    {
        $base = $total / (1 + $this->taxValue / 100);

        return $total - $base;
    }
}

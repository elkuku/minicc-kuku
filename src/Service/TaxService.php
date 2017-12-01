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
     * @var integer
     */
    private $taxValue;

    /**
     * TaxService constructor.
     *
     * @param integer $taxValue
     */
    public function __construct($taxValue)
    {
        $this->taxValue = $taxValue;
    }

    /**
     * @return int
     */
    public function getTaxValue()
    {
        return $this->taxValue;
    }
}

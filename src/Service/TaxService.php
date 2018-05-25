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

	/**
	 * Add the tax value to a given amount.
	 *
	 * @param float $value
	 *
	 * @return float
	 */
	public function getValueConTax(float $value): float
	{
		return round($value * (1 + $this->getTaxValue() / 100), 2);
	}
}

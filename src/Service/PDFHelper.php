<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 13.01.19
 * Time: 13:18
 */

namespace App\Service;

class PDFHelper
{
	/**
	 * @var string
	 */
	private $root;

	public function __construct(string $root)
	{
		$this->root = $root;
	}

	public function getRoot(): string
	{
		return $this->root;
	}
}

<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 25.05.18
 * Time: 14:52
 */

namespace App\Helper;

/**
 * Class BreadcrumbTrait
 */
trait BreadcrumbTrait
{
	/**
	 * @var array
	 */
	private $breadcrumbs = [];

	/**
	 * @param string $text
	 * @param string $link
	 *
	 * @return $this
	 */
	protected function addBreadcrumb(string $text, string $link = '')
	{
		$this->initBreadcrumbs();

		$this->breadcrumbs[$text] = $link;

		return $this;
	}

	/**
	 * @return array
	 */
	protected function getBreadcrumbs(): array
	{
		return $this->initBreadcrumbs()->breadcrumbs;
	}

	/**
	 * @return $this
	 */
	private function initBreadcrumbs()
	{
		if (!$this->breadcrumbs)
		{
			$this->breadcrumbs = ['Inicio' => 'welcome'];
		}

		return $this;
	}
}
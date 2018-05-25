<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Helper\Paginator\PaginatorOptions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseController
 */
abstract class AbstractController extends Controller
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
	protected function addBreadcrumb($text, $link = '')
	{
		$this->initBreadcrumbs();

		$this->breadcrumbs[$text] = $link;

		return $this;
	}

	/**
	 * @return array
	 */
	protected function getBreadcrumbs()
	{
		return $this->initBreadcrumbs()->breadcrumbs;
	}

	/**
	 * Get pagination options from request
	 *
	 * @param Request $request
	 *
	 * @return PaginatorOptions
	 */
	protected function getPaginatorOptions(Request $request)
	{
		$options = $request->get('paginatorOptions');

		$paginatorOptions = (new PaginatorOptions)
			->setPage(isset($options['page']) && $options['page'] ? (int) $options['page'] : 1)
			->setLimit(isset($options['limit']) && $options['limit'] ? (int) $options['limit'] : getenv('list_limit'))
			->setOrder(isset($options['order']) && $options['order'] ? $options['order'] : 'id')
			->setOrderDir(isset($options['orderDir']) && $options['orderDir'] ? $options['orderDir'] : 'ASC')
			->setCriteria(isset($options['criteria']) ? $options['criteria'] : []);

		return $paginatorOptions;
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

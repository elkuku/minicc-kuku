<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 18.06.18
 * Time: 07:54
 */

namespace App\Helper\Paginator;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait PaginatorRepoTrait
{
	/**
	 * @param Query   $dql
	 * @param integer $page
	 * @param integer $limit
	 *
	 * @return Paginator
	 */
	public function paginate(Query $dql, $page = 1, $limit = 5)
	{
		$paginator = new Paginator($dql);

		$paginator->getQuery()
			->setFirstResult($limit * ($page - 1))
			->setMaxResults($limit);

		return $paginator;
	}
}

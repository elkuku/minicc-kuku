<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class AbstractRepository
 */
class AbstractRepository extends EntityRepository
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

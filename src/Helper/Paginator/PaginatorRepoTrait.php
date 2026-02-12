<?php

declare(strict_types=1);

namespace App\Helper\Paginator;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait PaginatorRepoTrait
{
    /**
     * @return Paginator<Query>
     */
    public function paginate(
        Query $dql,
        int $page = 1,
        int $limit = 5
    ): Paginator
    {
        /** @var Paginator<Query> $paginator */
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }
}

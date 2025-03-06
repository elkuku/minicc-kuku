<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 25.05.18
 * Time: 15:08.
 */

namespace App\Helper\Paginator;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

trait PaginatorTrait
{
    /**
     * Get pagination options from request.
     */
    protected function getPaginatorOptions(
        Request $request,
        #[Autowire('%env(LIST_LIMIT)%')]
        int     $listLimit
    ): PaginatorOptions
    {
        $options = $request->get('paginatorOptions');

        return (new PaginatorOptions())
            ->setPage(
                isset($options['page']) && $options['page']
                    ? (int)$options['page'] : 1
            )
            ->setLimit(
                isset($options['limit']) && $options['limit']
                    ? (int)$options['limit'] : $listLimit
            )
            ->setOrder(
                isset($options['order']) && $options['order']
                    ? $options['order'] : 'id'
            )
            ->setOrderDir(
                isset($options['orderDir']) && $options['orderDir']
                    ? $options['orderDir'] : 'ASC'
            )
            ->setCriteria($options['criteria'] ?? []);
    }
}

<?php

namespace App\Repository;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransactionRepository
 *
 * @ORM\Entity
 */
class StoreRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getActive()
    {
        return $this->createQueryBuilder('s')
            ->where('s.valAlq > :val')
            ->setParameter('val', 0)
            ->getQuery()
            ->getResult();
    }
}

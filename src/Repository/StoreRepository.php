<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Store;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Store|null find($id, $lockMode = null, $lockVersion = null)
 * @method Store|null findOneBy(array $criteria, array $orderBy = null)
 * @method Store[]    findAll()
 * @method Store[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<StoreRepository>
 */
#[ORM\Entity]
class StoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        /**
         * @var class-string<StoreRepository>
         */
        $className = Store::class;
        parent::__construct($registry, $className);
    }

    /**
     * @return Store[]
     */
    public function getActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user IS NOT NULL')
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

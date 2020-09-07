<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Repository;

use App\Entity\Contract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contract|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contract|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contract[]    findAll()
 * @method Contract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    /**
     * @param int $storeId
     * @param int $year
     *
     * @return Contract[]
     */
    public function findContracts($storeId = 0, $year = 0): array
    {
        $query = $this->createQueryBuilder('c');

        $query->where('c.id > 1');

        if ($storeId) {
            $query->andWhere('c.storeNumber = :storeId')
                ->setParameter('storeId', $storeId);
        }

        if ($year) {
            $query->andWhere('YEAR(c.date) = :year')
                ->setParameter('year', $year);
        }

        $query->addOrderBy('c.date', 'DESC');
        $query->addOrderBy('c.storeNumber', 'ASC');

        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function findPlantilla()
    {
        $data = $this->matching(
            Criteria::create()->where(
                Criteria::expr()->eq('id', 1)
            )
        );

        return $data[0];
    }
}

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
 *
 * @extends ServiceEntityRepository<ContractRepository>
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        /**
         * @var class-string<ContractRepository>
         */
        $className = Contract::class;
        parent::__construct($registry, $className);
    }

    /**
     * @return Contract[]
     */
    public function findContracts(int $storeId = 0, int $year = 0): array
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

    public function findTemplate(): mixed
    {
        $data = $this->matching(
            Criteria::create()->where(
                Criteria::expr()->eq('id', 1)
            )
        );

        return $data[0];
    }
}

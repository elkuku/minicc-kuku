<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Repository;

use App\Entity\TransactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TransactionType|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionType|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionType[]    findAll()
 * @method TransactionType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<TransactionTypeRepository>
 */
class TransactionTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        /**
         * @var class-string<TransactionTypeRepository>
         */
        $className = TransactionType::class;
        parent::__construct($registry, $className);
    }
}

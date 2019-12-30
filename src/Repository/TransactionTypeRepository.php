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
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TransactionType|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionType|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionType[]    findAll()
 * @method TransactionType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionTypeRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, TransactionType::class);
	}
}

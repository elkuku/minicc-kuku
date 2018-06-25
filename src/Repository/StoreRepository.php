<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Repository;

use App\Entity\Store;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * TransactionRepository
 *
 * @ORM\Entity
 *
 * @method Store|null find($id, $lockMode = null, $lockVersion = null)
 * @method Store|null findOneBy(array $criteria, array $orderBy = null)
 * @method Store[]    findAll()
 * @method Store[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StoreRepository extends ServiceEntityRepository
{
	public function __construct(RegistryInterface $registry)
	{
		parent::__construct($registry, Store::class);
	}

	/**
	 * @return Store[]
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

<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Repository;

use App\Entity\UserState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserState|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserState|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserState[]    findAll()
 * @method UserState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStateRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, UserState::class);
	}
}

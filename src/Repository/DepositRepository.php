<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Repository;

use App\Entity\Deposit;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * DepositRepository
 * @method Deposit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deposit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deposit[]    findAll()
 * @method Deposit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepositRepository extends ServiceEntityRepository
{
	use PaginatorRepoTrait;

	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Deposit::class);
	}

	/**
	 * @param Deposit $deposit
	 *
	 * @return boolean
	 */
	public function has(Deposit $deposit): bool
	{
		return $this->findOneBy(
			[
				'date'     => $deposit->getDate(),
				'document' => $deposit->getDocument(),
			]
		) ? true : false;
	}

	/**
	 * @param PaginatorOptions $options
	 *
	 * @return \Doctrine\ORM\Tools\Pagination\Paginator
	 */
	public function getPaginatedList(PaginatorOptions $options): \Doctrine\ORM\Tools\Pagination\Paginator
	{
		$query = $this->createQueryBuilder('d')
			->orderBy('d.' . $options->getOrder(), $options->getOrderDir());

		if ($options->searchCriteria('amount'))
		{
			$query->andWhere('d.amount = :amount')
				->setParameter('amount', (float) $options->searchCriteria('amount'));
		}

		if ($options->searchCriteria('document'))
		{
			$query->andWhere('d.document LIKE :document')
				->setParameter('document', '%' . (int) $options->searchCriteria('document') . '%');
		}

		if ($options->searchCriteria('date_from'))
		{
			$query->andWhere('d.date >= :date_from')
				->setParameter('date_from', $options->searchCriteria('date_from'));
		}

		if ($options->searchCriteria('date_to'))
		{
			$query->andWhere('d.date <= :date_to')
				->setParameter('date_to', $options->searchCriteria('date_to'));
		}

		$query->addSelect('(SELECT t.id FROM App:Transaction t WHERE t.depId = d.id) AS tr_id');

		$query = $query->getQuery();

		return $this->paginate($query, $options->getPage(), $options->getLimit());
	}

	/**
	 * @param integer $documentId
	 *
	 * @return array
	 */
	public function lookup($documentId): array
	{
		return $this->createQueryBuilder('d')
			->where('d.document LIKE :document')
			->setParameter('document', '%' . (int) $documentId . '%')
			->addSelect('(SELECT t.id FROM App:Transaction t WHERE t.depId = d.id) AS tr_id')
			->getQuery()
			->getResult();
	}
}

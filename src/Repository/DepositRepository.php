<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deposit;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * DepositRepository.
 *
 * @method Deposit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deposit|null findOneBy(array<string, mixed> $criteria, ?array<string, string> $orderBy = null)
 * @method Deposit[]    findAll()
 * @method Deposit[]    findBy(array<string, mixed> $criteria, ?array<string, string> $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Deposit>
 */
class DepositRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deposit::class);
    }

    public function has(Deposit $deposit): bool
    {
        return (bool)$this->findOneBy(
            [
                'date' => $deposit->getDate(),
                'document' => $deposit->getDocument(),
            ]
        );
    }

    /**
     * @return Paginator<Query>
     */
    public function getPaginatedList(PaginatorOptions $options): Paginator
    {
        $query = $this->createQueryBuilder('d')
            ->orderBy('d.'.$options->getOrder(), $options->getOrderDir());

        if ($options->searchCriteria('amount') !== '' && $options->searchCriteria('amount') !== '0') {
            $query->andWhere('d.amount = :amount')
                ->setParameter(
                    'amount',
                    (float)$options->searchCriteria('amount')
                );
        }

        if ($options->searchCriteria('document') !== '' && $options->searchCriteria('document') !== '0') {
            $query->andWhere('d.document LIKE :document')
                ->setParameter(
                    'document',
                    '%'.(int)$options->searchCriteria('document')
                    .'%'
                );
        }

        if ($options->searchCriteria('date_from') !== '' && $options->searchCriteria('date_from') !== '0') {
            $query->andWhere('d.date >= :date_from')
                ->setParameter(
                    'date_from',
                    $options->searchCriteria('date_from')
                );
        }

        if ($options->searchCriteria('date_to') !== '' && $options->searchCriteria('date_to') !== '0') {
            $query->andWhere('d.date <= :date_to')
                ->setParameter('date_to', $options->searchCriteria('date_to'));
        }

        $query->addSelect(
            '(SELECT t.id FROM App\Entity\Transaction t WHERE t.depId = d.id) AS tr_id'
        );

        $query = $query->getQuery();

        return $this->paginate(
            $query,
            $options->getPage(),
            $options->getLimit()
        );
    }

    /**
     * @return Deposit[]
     */
    public function lookup(int $documentId): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.document LIKE :document')
            ->setParameter('document', '%'.$documentId.'%')
            ->addSelect(
                '(SELECT t.id FROM App\Entity\Transaction t WHERE t.depId = d.id) AS tr_id'
            )
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<float>
     */
    public function search(int $documentId): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.transaction', 'tr')
            ->andWhere('d.document LIKE :document')
            ->andWhere('tr.id IS NULL')
            ->setParameter('document', '%'.$documentId.'%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}

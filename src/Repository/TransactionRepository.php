<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Repository;

use App\Entity\Store;
use App\Entity\Transaction;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use function count;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[ORM\Entity]
class TransactionRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @return Transaction[]
     */
    public function findByStoreAndYear(Store $store, int $year): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.store = :store')
            ->andWhere('YEAR(p.date) = :year')
            ->setParameter('store', $store->getId())
            ->setParameter('year', $year)
            ->orderBy('p.date, p.type', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getSaldos(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t as data, SUM(t.amount) AS amount')
            ->groupBy('t.store')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSaldo(Store $store): ?float
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount) AS amount')
            ->where('t.store = :store')
            ->setParameter('store', $store->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSaldoAnterior(Store $store, $year): mixed
    {
        $year ?: date('Y');

        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.store = :store')
            ->andWhere('YEAR(t.date) < :year')
            ->setParameter('store', $store->getId())
            ->setParameter('year', $year)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSaldoALaFecha(Store $store, $date): mixed
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.store = :store')
            ->andWhere('t.date < :date')
            ->setParameter('store', $store->getId())
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMonthPayments(
        Store $store,
        string $month,
        string $year
    ): array {
        return $this->createQueryBuilder('p')
            ->where('p.store = :store')
            ->andWhere('MONTH(p.date) = :month')
            ->andWhere('YEAR(p.date) = :year')
            ->andWhere('p.type = 2')
            ->setParameter('store', $store->getId())
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Transaction[]
     */
    public function getPagosPorAno(int $year): array
    {
        $transactions = $this->createQueryBuilder('t')
            ->where('YEAR(t.date) = :year')
            ->andWhere('t.type = :type')
            ->setParameter('year', $year)
            ->setParameter('type', 2)
            ->getQuery()
            ->getResult();

        if (!(is_countable($transactions) ? count($transactions) : 0)) {
            return [];
        }

        $payments = [];

        /** @type Transaction $transaction */
        foreach ($transactions as $transaction) {
            $mes = (int)$transaction->getDate()->format('m');
            $day = (int)$transaction->getDate()->format('d');

            $payments[$transaction->getStore()->getId()][$mes][$day][]
                = $transaction;
        }

        return $payments;
    }

    public function getRawList(PaginatorOptions $options): Paginator
    {
        $criteria = $options->getCriteria();

        $query = $this->createQueryBuilder('t')
            ->orderBy('t.'.$options->getOrder(), $options->getOrderDir());

        if (isset($criteria['type']) && $criteria['type']) {
            $query->where('t.type = :type')
                ->setParameter('type', (int)$criteria['type']);
        }

        if ($options->searchCriteria('amount')) {
            $query->andWhere('t.amount = :amount')
                ->setParameter(
                    'amount',
                    (float)$options->searchCriteria('amount')
                );
        }

        if ($options->searchCriteria('store')) {
            $query->andWhere('t.store = :store')
                ->setParameter('store', (int)$options->searchCriteria('store'));
        }

        if ($options->searchCriteria('date_from')) {
            $query->andWhere('t.date >= :date_from')
                ->setParameter(
                    'date_from',
                    $options->searchCriteria('date_from')
                );
        }

        if ($options->searchCriteria('date_to')) {
            $query->andWhere('t.date <= :date_to')
                ->setParameter('date_to', $options->searchCriteria('date_to'));
        }

        $query = $query->getQuery();

        return $this->paginate(
            $query,
            $options->getPage(),
            $options->getLimit()
        );
    }

    public function getLastRecipeNo(): int
    {
        try {
            $number = (int)$this->createQueryBuilder('t')
                ->select('MAX(t.recipeNo)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (Exception) {
            $number = 0;
        }

        return $number;
    }
}

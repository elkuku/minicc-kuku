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
use App\Entity\User;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use function count;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<TransactionRepository>
 */
#[Entity]
class TransactionRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        /**
         * @var class-string<TransactionRepository>
         */
        $className = Transaction::class;
        parent::__construct($registry, $className);
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

    /**
     * @return Transaction[]
     */
    public function findByStoreYearAndUser(
        Store $store,
        int $year,
        User $user
    ): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.store = :store')
            ->andWhere('YEAR(p.date) = :year')
            ->andWhere('p.user = :user')
            ->setParameter('store', $store->getId())
            ->setParameter('year', $year)
            ->setParameter('user', $user)
            ->orderBy('p.date, p.type', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<float>
     */
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
        return (float) $this->createQueryBuilder('t')
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
    public function getSaldoAnterior(Store $store, int $year): mixed
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
     */
    public function getSaldoALaFecha(Store $store, string $date): mixed
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

    /**
     * @return array<float>
     */
    public function findMonthPayments(
        Store $store,
        int $month,
        int $year
    ): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.store = :store')
            ->andWhere('MONTH(p.date) = :month')
            ->andWhere('YEAR(p.date) = :year')
            ->andWhere('p.type = 2 OR p.type = 4')
            ->setParameter('store', $store->getId())
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return  array<int|string, array<int, array<int, array<int, Transaction>>>>
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

        if (! (is_countable($transactions) ? count($transactions) : 0)) {
            return [];
        }

        $payments = [];

        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $mes = (int) $transaction->getDate()->format('m');
            $day = (int) $transaction->getDate()->format('d');

            $payments[$transaction->getStore()->getId()][$mes][$day][]
                = $transaction;
        }

        return $payments;
    }

    /**
     * @return Paginator<Query>
     */
    public function getRawList(PaginatorOptions $options): Paginator
    {
        $criteria = $options->getCriteria();

        $query = $this->createQueryBuilder('t')
            ->orderBy('t.' . $options->getOrder(), $options->getOrderDir());

        if (isset($criteria['type']) && $criteria['type']) {
            $query->where('t.type = :type')
                ->setParameter('type', (int) $criteria['type']);
        }

        if ($options->searchCriteria('amount')) {
            $query->andWhere('t.amount = :amount')
                ->setParameter(
                    'amount',
                    (float) $options->searchCriteria('amount')
                );
        }

        if ($options->searchCriteria('store')) {
            $query->andWhere('t.store = :store')
                ->setParameter('store', (int) $options->searchCriteria('store'));
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

        if ($options->searchCriteria('recipe')) {
            $query->andWhere('t.recipeNo = :recipe')
                ->setParameter('recipe', (int) $options->searchCriteria('recipe'));
        }

        if ($options->searchCriteria('comment')) {
            $query->andWhere('t.comment LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $options->searchCriteria('comment') . '%');
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
            $number = (int) $this->createQueryBuilder('t')
                ->select('MAX(t.recipeNo)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (Exception) {
            $number = 0;
        }

        return $number;
    }
}

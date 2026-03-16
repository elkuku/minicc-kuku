<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Store;
use App\Entity\Transaction;
use App\Entity\User;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use App\Type\TransactionType;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Clock\ClockInterface;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array<string, mixed> $criteria, ?array<string, string> $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array<string, mixed> $criteria, ?array<string, string> $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry, private readonly ClockInterface $clock)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @return Transaction[]
     */
    public function findByStoreAndYear(Store $store, int $year): array
    {
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('p')
            ->where('p.store = :store')
            ->andWhere('YEAR(p.date) = :year')
            ->setParameter('store', $store->getId())
            ->setParameter('year', $year)
            ->orderBy('p.date, p.type', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
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
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('p')
            ->where('p.store = :store')
            ->andWhere('YEAR(p.date) = :year')
            ->andWhere('p.user = :user')
            ->setParameter('store', $store->getId())
            ->setParameter('year', $year)
            ->setParameter('user', $user)
            ->orderBy('p.date, p.type', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return array<float>
     */
    public function getSaldos(): array
    {
        /** @var array<float> $result */
        $result = $this->createQueryBuilder('t')
            ->select('t as data, SUM(t.amount) AS amount')
            ->groupBy('t.store')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSaldo(Store $store): ?float
    {
        return (float)$this->createQueryBuilder('t')
            ->select('SUM(t.amount) AS amount')
            ->where('t.store = :store')
            ->setParameter('store', $store->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return int|mixed|string
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSaldoAnterior(Store $store, int $year): mixed
    {
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
     * @param array<int> $storeIds
     * @return array<int, mixed>
     */
    public function getSaldoALaFechaByStores(array $storeIds, string $date): array
    {
        if ($storeIds === []) {
            return [];
        }

        /** @var array<array{storeId: string, total: mixed}> $rows */
        $rows = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.store) as storeId, SUM(t.amount) as total')
            ->andWhere('t.store IN (:storeIds)')
            ->andWhere('t.date < :date')
            ->setParameter('storeIds', $storeIds)
            ->setParameter('date', $date)
            ->groupBy('t.store')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['storeId']] = $row['total'];
        }

        return $result;
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
        /** @var array<float> $result */
        $result = $this->createQueryBuilder('p')
            ->where('p.store = :store')
            ->andWhere('MONTH(p.date) = :month')
            ->andWhere('YEAR(p.date) = :year')
            ->andWhere('p.type =  :type1 OR p.type = :type2')
            ->setParameter('store', $store->getId())
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->setParameter('type1', TransactionType::payment)
            ->setParameter('type2', TransactionType::adjustment)
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param array<int> $storeIds
     * @return array<int, Transaction[]>
     */
    public function findMonthPaymentsByStores(array $storeIds, int $month, int $year): array
    {
        if ($storeIds === []) {
            return [];
        }

        /** @var Transaction[] $transactions */
        $transactions = $this->createQueryBuilder('p')
            ->andWhere('p.store IN (:storeIds)')
            ->andWhere('MONTH(p.date) = :month')
            ->andWhere('YEAR(p.date) = :year')
            ->andWhere('p.type = :type1 OR p.type = :type2')
            ->setParameter('storeIds', $storeIds)
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->setParameter('type1', TransactionType::payment)
            ->setParameter('type2', TransactionType::adjustment)
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($transactions as $transaction) {
            $result[(int) $transaction->getStore()->getId()][] = $transaction;
        }

        return $result;
    }

    /**
     * @return Transaction[]
     */
    public function findByDate(int $year, int $month): array
    {
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('t')
            ->andWhere('YEAR(t.date) = :year')
            ->andWhere('MONTH(t.date) = :month')
            ->andWhere('t.type = :type')
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->setParameter('type', TransactionType::payment)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param array<int> $ids
     * @return Transaction[]
     */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return array<int|string, array<int, array<int, array<int, Transaction>>>>
     */
    public function getPagosPorAno(int $year): array
    {
        /**
         * @var Transaction[] $transactions
         */
        $transactions = $this->createQueryBuilder('t')
            ->where('YEAR(t.date) = :year')
            ->andWhere('t.type = :type')
            ->setParameter('year', $year)
            ->setParameter('type', TransactionType::payment)
            ->getQuery()
            ->getResult();

        $payments = [];

        foreach ($transactions as $transaction) {
            $mes = (int)$transaction->getDate()->format('m');
            $day = (int)$transaction->getDate()->format('d');

            $payments[(int)$transaction->getStore()->getId()][$mes][$day][]
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
            ->orderBy('t.'.$options->getOrder(), $options->getOrderDir());

        if (isset($criteria['type']) && $criteria['type']) {
            $query->where('t.type = :type')
                ->setParameter('type', (int)$criteria['type']);
        }

        $this->applySearchFilters($query, $options);

        return $this->paginate(
            $query->getQuery(),
            $options->getPage(),
            $options->getLimit()
        );
    }

    private function hasCriteria(PaginatorOptions $options, string $key): bool
    {
        $value = $options->searchCriteria($key);

        return $value !== '' && $value !== '0';
    }

    private function applySearchFilters(QueryBuilder $query, PaginatorOptions $options): void
    {
        if ($this->hasCriteria($options, 'amount')) {
            $query->andWhere('t.amount = :amount')
                ->setParameter('amount', (float)$options->searchCriteria('amount'));
        }

        if ($this->hasCriteria($options, 'store')) {
            $query->andWhere('t.store = :store')
                ->setParameter('store', (int)$options->searchCriteria('store'));
        }

        if ($this->hasCriteria($options, 'date_from')) {
            $query->andWhere('t.date >= :date_from')
                ->setParameter('date_from', $options->searchCriteria('date_from'));
        }

        if ($this->hasCriteria($options, 'date_to')) {
            $query->andWhere('t.date <= :date_to')
                ->setParameter('date_to', $options->searchCriteria('date_to'));
        }

        if ($this->hasCriteria($options, 'recipe')) {
            $query->andWhere('t.recipeNo = :recipe')
                ->setParameter('recipe', (int)$options->searchCriteria('recipe'));
        }

        if ($this->hasCriteria($options, 'comment')) {
            $query->andWhere('t.comment LIKE :searchTerm')
                ->setParameter('searchTerm', '%'.$options->searchCriteria('comment').'%');
        }
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

    public function checkChargementRequired(): bool
    {
        $now = $this->clock->now();
        $currentYear = (int) $now->format('Y');
        $lastChargedYear = (int)$this->getLastChargementDate()->format('Y');

        if ($lastChargedYear < $currentYear) {
            return true;
        }

        $currentMonth = (int) $now->format('m');
        $lastChargedMonth = (int)$this->getLastChargementDate()->format('m');

        if (12 === $currentMonth && 1 === $lastChargedMonth) {
            return true;
        }

        return $lastChargedMonth < $currentMonth;
    }

    public function getLastChargementDate(): DateTime
    {
        $date = $this->createQueryBuilder('t')
            ->select('MAX(t.date)')
            ->andWhere('t.type = :type')
            ->setParameter('type', TransactionType::rent)
            ->getQuery()
            ->getSingleScalarResult();

        return new DateTime((string)$date);
    }
}

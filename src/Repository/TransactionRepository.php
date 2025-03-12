<?php

declare(strict_types=1);

namespace App\Repository;

use Exception;
use DateTime;
use App\Entity\Store;
use App\Entity\Transaction;
use App\Entity\User;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use App\Type\TransactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

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
         * @var class-string<TransactionRepository> $className
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
        int   $year,
        User  $user
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
        int   $month,
        int   $year
    ): array
    {
        return $this->createQueryBuilder('p')
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
    }

    /**
     * @return Transaction[]
     */
    public function findByDate(int $year, int $month): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('YEAR(t.date) = :year')
            ->andWhere('MONTH(t.date) = :month')
            ->andWhere('t.type = :type')
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->setParameter('type', TransactionType::payment)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<int> $ids
     * @return Transaction[]
     */
    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('t')
            //->andWhere('t.id IN (:ids)')
            ->andWhere('t.id IN(' . implode(',', $ids) . ')')
            //   ->setParameter('ids', implode(',', $ids))
            ->getQuery()
            ->getResult();
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

        if ($options->searchCriteria('recipe')) {
            $query->andWhere('t.recipeNo = :recipe')
                ->setParameter('recipe', (int)$options->searchCriteria('recipe'));
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
            $number = (int)$this->createQueryBuilder('t')
                ->select('MAX(t.recipeNo)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (Exception) {
            $number = 0;
        }

        return $number;
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

    public function checkChargementRequired(): bool
    {
        $currentMonth = (int)(new DateTime())->format('m');
        $lastChargedMonth = (int)$this->getLastChargementDate()->format('m');

        if (12 === $currentMonth && 1 === $lastChargedMonth) {
            return true;
        }

        return $lastChargedMonth < $currentMonth;
    }
}

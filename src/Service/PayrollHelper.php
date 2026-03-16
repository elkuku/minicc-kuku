<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Store;
use App\Entity\Transaction;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;

class PayrollHelper
{
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly TransactionRepository $transactionRepository,
    ) {}

    /**
     * @return array{factDate: string, prevDate: string, stores: Store[], storeData: array<int, array{saldoIni: mixed, transactions: Transaction[]}>}
     */
    public function getData(
        int $year,
        int $month,
        int $storeId = 0
    ): array
    {
        $stores = $this->storeRepository->findAll();

        $factDate = $year.'-'.$month.'-1';

        if (1 === $month) {
            $prevYear = $year - 1;
            $prevMonth = 12;
        } else {
            $prevYear = $year;
            $prevMonth = $month - 1;
        }

        $prevDate = $prevYear.'-'.$prevMonth.'-01';

        $selectedStores = [];
        foreach ($stores as $store) {
            if ($storeId && $store->getId() !== $storeId) {
                continue;
            }

            $selectedStores[] = $store;
        }

        $selectedIds = array_map(static fn (Store $s): int => (int) $s->getId(), $selectedStores);
        $prevDateStr = $prevYear.'-'.$prevMonth.'-01';

        $saldos = $this->transactionRepository->getSaldoALaFechaByStores($selectedIds, $prevDateStr);
        $payments = $this->transactionRepository->findMonthPaymentsByStores($selectedIds, $prevMonth, $prevYear);

        $storeData = [];
        foreach ($selectedStores as $store) {
            $id = (int) $store->getId();
            $storeData[$id]['saldoIni'] = $saldos[$id] ?? null;
            $storeData[$id]['transactions'] = $payments[$id] ?? [];
        }

        return [
            'factDate' => $factDate,
            'prevDate' => $prevDate,
            'stores' => $selectedStores,
            'storeData' => $storeData,
        ];
    }
}

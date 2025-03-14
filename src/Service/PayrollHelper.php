<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Store;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;

class PayrollHelper
{
    public function __construct(
        private readonly StoreRepository       $storeRepository,
        private readonly TransactionRepository $transactionRepository,
    )
    {
    }

    /**
     * @return array{factDate: string, prevDate: string, stores: Store[], storeData: array<string|int, array{saldoIni: mixed, transactions: float[]}>}
     */
    public function getData(
        int $year,
        int $month,
        int $storeId = 0
    ): array
    {
        $stores = $this->storeRepository->findAll();

        $factDate = $year . '-' . $month . '-1';

        if (1 === $month) {
            $prevYear = $year - 1;
            $prevMonth = 12;
        } else {
            $prevYear = $year;
            $prevMonth = $month - 1;
        }

        $prevDate = $prevYear . '-' . $prevMonth . '-01';

        $storeData = [];
        $selectedStores = [];

        foreach ($stores as $store) {
            if ($storeId && $store->getId() !== $storeId) {
                continue;
            }

            $storeData[$store->getId()]['saldoIni']
                = $this->transactionRepository->getSaldoALaFecha(
                $store,
                $prevYear . '-' . $prevMonth . '-01'
            );

            $storeData[$store->getId()]['transactions']
                = $this->transactionRepository->findMonthPayments(
                $store,
                $prevMonth,
                $prevYear
            );

            $selectedStores[] = $store;
        }

        return [
            'factDate' => $factDate,
            'prevDate' => $prevDate,
            'stores' => $selectedStores,
            'storeData' => $storeData,
        ];
    }
}

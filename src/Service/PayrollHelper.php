<?php

namespace App\Service;

use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use JetBrains\PhpStorm\ArrayShape;

class PayrollHelper
{
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly TransactionRepository $transactionRepository,
    ) {
    }

    #[ArrayShape([
        'factDate'  => "string",
        'prevDate'  => "string",
        'stores'    => "array",
        'storeData' => "array",
    ])]
    public function getData(
        int $year,
        int $month,
        int $storeId = 0
    ): array {
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

        $storeData = [];
        $selectedStores = [];

        foreach ($stores as $store) {
            if ($storeId && $store->getId() !== $storeId) {
                continue;
            }

            $storeData[$store->getId()]['saldoIni']
                = $this->transactionRepository->getSaldoALaFecha(
                $store,
                $prevYear.'-'.$prevMonth.'-01'
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
            'factDate'  => $factDate,
            'prevDate'  => $prevDate,
            'stores'    => $selectedStores,
            'storeData' => $storeData,
        ];
    }
}

<?php

namespace App\Controller;

use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\ChartBuilderService;
use App\Service\TaxService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/', name: 'welcome', methods: ['GET'])]
class DefaultController extends BaseController
{
    public function __invoke(
        StoreRepository       $storeRepository,
        TransactionRepository $transactionRepository,
        TaxService            $taxService,
        ChartBuilderService   $chartBuilderService
    ): Response
    {
        $user = $this->getUser();
        $balances = null;
        $chartData = [
            'headers' => [],
            'monthsDebt' => [],
            'balances' => [],
        ];
        if ($user) {
            foreach ($storeRepository->getActive() as $store) {
                $balance = $transactionRepository->getSaldo($store);
                $chartData['headers'][] = 'Local ' . $store->getId();
                $valAlq = $taxService->getValueConTax($store->getValAlq());

                $chartData['monthsDebt'][] = $valAlq
                    ? round(-$balance / $valAlq, 1)
                    : 0;
                $chartData['balances'][] = -$balance;

                $s = new \stdClass();
                $s->amount = $balance;
                $s->store = $store;

                $balances[] = $s;
            }
        }

        return $this->render(
            'default/index.html.twig',
            [
                'stores' => $user?->getStores(),
                'balances' => $balances,
                'chartBalances' => $chartBuilderService->getDashboardChart(
                    'Saldo en $',
                    $chartData['headers'],
                    $chartData['balances']
                ),
                'chartMonthsDebt' => $chartBuilderService->getDashboardChart(
                    'Meses de deuda',
                    $chartData['headers'],
                    $chartData['monthsDebt']
                ),
                'chargementRequired' => $transactionRepository->checkChargementRequired(),
            ]
        );
    }
}

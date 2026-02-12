<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\ChartBuilderService;
use App\Service\TaxService;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/', name: 'welcome', methods: ['GET'])]
class Welcome extends BaseController
{
    public function __construct(private readonly StoreRepository $storeRepository, private readonly TransactionRepository $transactionRepository, private readonly TaxService $taxService, private readonly ChartBuilderService $chartBuilderService) {}

    public function __invoke(): Response
    {
        $user = $this->getUser();
        $balances = null;
        $chartData = [
            'headers' => [],
            'monthsDebt' => [],
            'balances' => [],
        ];
        if ($user) {
            foreach ($this->storeRepository->getActive() as $store) {
                $balance = $this->transactionRepository->getSaldo($store);
                $chartData['headers'][] = 'Local '.$store->getId();
                $valAlq = $this->taxService->addTax($store->getValAlq());

                $chartData['monthsDebt'][] = $valAlq !== 0.0
                    ? round(-$balance / $valAlq, 1)
                    : 0;
                $chartData['balances'][] = -$balance;

                $s = new stdClass();
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
                'chartBalances' => $this->chartBuilderService->getDashboardChart(
                    'Saldo en $',
                    $chartData['headers'],
                    $chartData['balances']
                ),
                'chartMonthsDebt' => $this->chartBuilderService->getDashboardChart(
                    'Meses de deuda',
                    $chartData['headers'],
                    $chartData['monthsDebt']
                ),
                'chargementRequired' => $this->transactionRepository->checkChargementRequired(),
            ]
        );
    }
}

<?php

namespace App\Controller;

use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\ChartBuilderService;
use App\Service\TaxService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends BaseController
{
    #[Route(path: '/', name: 'welcome', methods: ['GET'])]
    public function index(
        StoreRepository $storeRepository,
        TransactionRepository $transactionRepository,
        TaxService $taxService,
        ChartBuilderService $chartBuilderService
    ): Response {
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
            ]
        );
    }

    #[Route(path: '/about', name: 'about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('default/about.html.twig');
    }

    #[Route(path: '/contact', name: 'contact', methods: ['GET'])]
    public function contact(): Response
    {
        return $this->render('default/contact.html.twig');
    }
}

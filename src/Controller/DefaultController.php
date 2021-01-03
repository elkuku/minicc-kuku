<?php

namespace App\Controller;

use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\TaxService;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="welcome")
     */
    public function index(
        StoreRepository $storeRepository,
        TransactionRepository $transactionRepository,
        TaxService $taxService,
        ChartBuilderInterface $chartBuilder
    ): Response {
        $user = $this->getUser();
        $balances = null;
        $chartData = [
            'headers'    => [],
            'monthsDebt' => [],
            'balances'   => [],
        ];

        if ($user) {
            foreach ($storeRepository->getActive() as $store) {
                $balance = $transactionRepository->getSaldo($store);
                $chartData['headers'][] = 'Local '.$store->getId();
                $valAlq = $taxService->getValueConTax($store->getValAlq());

                $chartData['monthsDebt'][] = $valAlq
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
                'stores'           => $user ? $user->getStores() : null,
                'balances'         => $balances,
                'chartBalances'    => $this->getChart(
                    'Saldo en $',
                    $chartData['headers'],
                    $chartData['balances'],
                    $chartBuilder
                ),
                'chartMonthsDebt'  => $this->getChart(
                    'Meses de deuda',
                    $chartData['headers'],
                    $chartData['monthsDebt'],
                    $chartBuilder
                ),
            ]
        );
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(): Response
    {
        return $this->render('default/about.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(): Response
    {
        return $this->render('default/contact.html.twig');
    }

    private function getChart(
        string $title,
        array $labels,
        array $data,
        ChartBuilderInterface $chartBuilder
    ): Chart {
        $bgColors = [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
        ];

        $borderColors = [
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
        ];

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData(
            [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'           => $title,
                        'backgroundColor' => $bgColors,
                        'borderColor'     => $borderColors,
                        'borderWidth'     => 1,
                        'data'            => $data,
                    ],
                ],
            ]
        );

        return $chart;
    }
}

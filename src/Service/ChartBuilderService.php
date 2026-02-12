<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ChartBuilderService
{
    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder
    ) {}

    /**
     * @param array<int, string> $labels
     * @param array<int, float> $data
     */
    public function getDashboardChart(
        string $title,
        array $labels,
        array $data
    ): Chart
    {
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

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData(
            [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $title,
                        'backgroundColor' => $bgColors,
                        'borderColor' => $borderColors,
                        'borderWidth' => 1,
                        'data' => $data,
                    ],
                ],
            ]
        );

        return $chart;
    }

    /**
     * @param array<string> $labels
     * @param array<int, float> $dataPayments
     * @param array<int, float> $dataRent
     */
    public function getStoreChart(
        array $labels,
        array $dataPayments,
        array $dataRent
    ): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData(
            [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Pagos',
                        'data' => $dataPayments,
                        'fill' => 'false',
                        'lineTension' => 0.1,
                        'backgroundColor' => 'rgba(75,192,192,0.4)',
                        'borderColor' => 'rgba(75,192,192,1)',
                    ],
                    [
                        'label' => 'Alquiler',
                        'data' => $dataRent,
                        'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                        'borderColor' => 'rgba(255, 206, 86, 0.2)',
                        'borderWidth' => 1,
                    ],
                ],
            ]
        );

        return $chart;
    }
}

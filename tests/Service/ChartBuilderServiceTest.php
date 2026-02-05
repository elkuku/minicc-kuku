<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\ChartBuilderService;
use PHPUnit\Framework\TestCase;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class ChartBuilderServiceTest extends TestCase
{
    public function testGetDashboardChartReturnsBarChartWithCorrectData(): void
    {
        $chartBuilder = $this->createStub(ChartBuilderInterface::class);
        $chartBuilder->method('createChart')
            ->willReturnCallback(fn(string $type) => new Chart($type));

        $service = new ChartBuilderService($chartBuilder);
        $labels = ['Jan', 'Feb', 'Mar'];
        $data = [10.0, 20.0, 30.0];

        $chart = $service->getDashboardChart('Revenue', $labels, $data);

        self::assertSame('bar', $chart->getType());

        $chartData = $chart->getData();
        self::assertSame($labels, $chartData['labels']);
        self::assertCount(1, $chartData['datasets']);
        self::assertSame('Revenue', $chartData['datasets'][0]['label']);
        self::assertSame($data, $chartData['datasets'][0]['data']);
    }

    public function testGetStoreChartReturnsLineChartWithTwoDatasets(): void
    {
        $chartBuilder = $this->createStub(ChartBuilderInterface::class);
        $chartBuilder->method('createChart')
            ->willReturnCallback(fn(string $type) => new Chart($type));

        $service = new ChartBuilderService($chartBuilder);
        $labels = ['Week 1', 'Week 2'];
        $payments = [100.0, 150.0];
        $rent = [50.0, 50.0];

        $chart = $service->getStoreChart($labels, $payments, $rent);

        self::assertSame('line', $chart->getType());

        $chartData = $chart->getData();
        self::assertSame($labels, $chartData['labels']);
        self::assertCount(2, $chartData['datasets']);
        self::assertSame('Pagos', $chartData['datasets'][0]['label']);
        self::assertSame($payments, $chartData['datasets'][0]['data']);
        self::assertSame('Alquiler', $chartData['datasets'][1]['label']);
        self::assertSame($rent, $chartData['datasets'][1]['data']);
    }
}

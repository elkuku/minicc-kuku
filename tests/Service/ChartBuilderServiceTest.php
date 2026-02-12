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
            ->willReturnCallback(fn(string $type): Chart => new Chart($type));

        $service = new ChartBuilderService($chartBuilder);
        $labels = ['Jan', 'Feb', 'Mar'];
        $data = [10.0, 20.0, 30.0];

        $chart = $service->getDashboardChart('Revenue', $labels, $data);

        $this->assertSame('bar', $chart->getType());

        $chartData = $chart->getData();
        $this->assertSame($labels, $chartData['labels']);
        $this->assertCount(1, $chartData['datasets']);
        $this->assertSame('Revenue', $chartData['datasets'][0]['label']);
        $this->assertSame($data, $chartData['datasets'][0]['data']);
    }

    public function testGetStoreChartReturnsLineChartWithTwoDatasets(): void
    {
        $chartBuilder = $this->createStub(ChartBuilderInterface::class);
        $chartBuilder->method('createChart')
            ->willReturnCallback(fn(string $type): Chart => new Chart($type));

        $service = new ChartBuilderService($chartBuilder);
        $labels = ['Week 1', 'Week 2'];
        $payments = [100.0, 150.0];
        $rent = [50.0, 50.0];

        $chart = $service->getStoreChart($labels, $payments, $rent);

        $this->assertSame('line', $chart->getType());

        $chartData = $chart->getData();
        $this->assertSame($labels, $chartData['labels']);
        $this->assertCount(2, $chartData['datasets']);
        $this->assertSame('Pagos', $chartData['datasets'][0]['label']);
        $this->assertSame($payments, $chartData['datasets'][0]['data']);
        $this->assertSame('Alquiler', $chartData['datasets'][1]['label']);
        $this->assertSame($rent, $chartData['datasets'][1]['data']);
    }
}

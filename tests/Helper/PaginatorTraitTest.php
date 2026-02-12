<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\Paginator\PaginatorTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PaginatorTraitTest extends TestCase
{
    use PaginatorTrait;

    public function testDefaultValues(): void
    {
        $request = Request::create('/test');
        $options = $this->getPaginatorOptions($request, 25);

        $this->assertSame(1, $options->getPage());
        $this->assertSame(25, $options->getLimit());
        $this->assertSame('id', $options->getOrder());
        $this->assertSame('ASC', $options->getOrderDir());
        $this->assertSame([], $options->getCriteria());
    }

    public function testCustomValues(): void
    {
        $request = Request::create('/test', Request::METHOD_GET, [
            'paginatorOptions' => [
                'page' => '3',
                'limit' => '50',
                'order' => 'name',
                'orderDir' => 'DESC',
            ],
        ]);
        $options = $this->getPaginatorOptions($request, 25);

        $this->assertSame(3, $options->getPage());
        $this->assertSame(50, $options->getLimit());
        $this->assertSame('name', $options->getOrder());
        $this->assertSame('DESC', $options->getOrderDir());
    }

    public function testCriteriaPassthrough(): void
    {
        $request = Request::create('/test', Request::METHOD_GET, [
            'paginatorOptions' => [
                'criteria' => ['status' => 'active'],
            ],
        ]);
        $options = $this->getPaginatorOptions($request, 10);

        $this->assertSame(['status' => 'active'], $options->getCriteria());
    }

    public function testPartialOptions(): void
    {
        $request = Request::create('/test', Request::METHOD_GET, [
            'paginatorOptions' => [
                'page' => '2',
                'order' => 'date',
            ],
        ]);
        $options = $this->getPaginatorOptions($request, 15);

        $this->assertSame(2, $options->getPage());
        $this->assertSame(15, $options->getLimit());
        $this->assertSame('date', $options->getOrder());
        $this->assertSame('ASC', $options->getOrderDir());
    }

    public function testListLimitUsedAsDefault(): void
    {
        $request = Request::create('/test');
        $options = $this->getPaginatorOptions($request, 42);

        $this->assertSame(42, $options->getLimit());
    }
}

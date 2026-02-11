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

        self::assertSame(1, $options->getPage());
        self::assertSame(25, $options->getLimit());
        self::assertSame('id', $options->getOrder());
        self::assertSame('ASC', $options->getOrderDir());
        self::assertSame([], $options->getCriteria());
    }

    public function testCustomValues(): void
    {
        $request = Request::create('/test', 'GET', [
            'paginatorOptions' => [
                'page' => '3',
                'limit' => '50',
                'order' => 'name',
                'orderDir' => 'DESC',
            ],
        ]);
        $options = $this->getPaginatorOptions($request, 25);

        self::assertSame(3, $options->getPage());
        self::assertSame(50, $options->getLimit());
        self::assertSame('name', $options->getOrder());
        self::assertSame('DESC', $options->getOrderDir());
    }

    public function testCriteriaPassthrough(): void
    {
        $request = Request::create('/test', 'GET', [
            'paginatorOptions' => [
                'criteria' => ['status' => 'active'],
            ],
        ]);
        $options = $this->getPaginatorOptions($request, 10);

        self::assertSame(['status' => 'active'], $options->getCriteria());
    }

    public function testPartialOptions(): void
    {
        $request = Request::create('/test', 'GET', [
            'paginatorOptions' => [
                'page' => '2',
                'order' => 'date',
            ],
        ]);
        $options = $this->getPaginatorOptions($request, 15);

        self::assertSame(2, $options->getPage());
        self::assertSame(15, $options->getLimit());
        self::assertSame('date', $options->getOrder());
        self::assertSame('ASC', $options->getOrderDir());
    }

    public function testListLimitUsedAsDefault(): void
    {
        $request = Request::create('/test');
        $options = $this->getPaginatorOptions($request, 42);

        self::assertSame(42, $options->getLimit());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\Paginator\PaginatorOptions;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class PaginatorOptionsTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $options = new PaginatorOptions();

        self::assertSame(0, $options->getPage());
        self::assertSame(0, $options->getMaxPages());
        self::assertSame(10, $options->getLimit());
        self::assertSame('id', $options->getOrder());
        self::assertSame('ASC', $options->getOrderDir());
        self::assertSame([], $options->getCriteria());
    }

    public function testSetPage(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setPage(5);

        self::assertSame($options, $result);
        self::assertSame(5, $options->getPage());
    }

    public function testSetMaxPages(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setMaxPages(20);

        self::assertSame($options, $result);
        self::assertSame(20, $options->getMaxPages());
    }

    public function testSetLimit(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setLimit(25);

        self::assertSame($options, $result);
        self::assertSame(25, $options->getLimit());
    }

    public function testSetOrder(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setOrder('name');

        self::assertSame($options, $result);
        self::assertSame('name', $options->getOrder());
    }

    public function testSetOrderDirAsc(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setOrderDir('ASC');

        self::assertSame($options, $result);
        self::assertSame('ASC', $options->getOrderDir());
    }

    public function testSetOrderDirDesc(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setOrderDir('DESC');

        self::assertSame($options, $result);
        self::assertSame('DESC', $options->getOrderDir());
    }

    public function testSetOrderDirCaseInsensitive(): void
    {
        $options = new PaginatorOptions();

        $options->setOrderDir('desc');

        self::assertSame('desc', $options->getOrderDir());
    }

    public function testSetOrderDirThrowsOnInvalidValue(): void
    {
        $options = new PaginatorOptions();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Order dir must be ASC, DESC');

        $options->setOrderDir('INVALID');
    }

    public function testSetCriteria(): void
    {
        $options = new PaginatorOptions();
        $criteria = ['name' => 'John', 'status' => 'active'];

        $result = $options->setCriteria($criteria);

        self::assertSame($options, $result);
        self::assertSame($criteria, $options->getCriteria());
    }

    public function testSearchCriteriaExistingKey(): void
    {
        $options = new PaginatorOptions();
        $options->setCriteria(['name' => 'John', 'status' => 'active']);

        self::assertSame('John', $options->searchCriteria('name'));
        self::assertSame('active', $options->searchCriteria('status'));
    }

    public function testSearchCriteriaNonExistingKey(): void
    {
        $options = new PaginatorOptions();
        $options->setCriteria(['name' => 'John']);

        self::assertSame('', $options->searchCriteria('nonexistent'));
    }

    public function testSearchCriteriaEmptyCriteria(): void
    {
        $options = new PaginatorOptions();

        self::assertSame('', $options->searchCriteria('anykey'));
    }

    public function testFluentInterface(): void
    {
        $options = new PaginatorOptions();

        $result = $options
            ->setPage(1)
            ->setMaxPages(10)
            ->setLimit(20)
            ->setOrder('created_at')
            ->setOrderDir('DESC')
            ->setCriteria(['active' => 'true']);

        self::assertSame($options, $result);
        self::assertSame(1, $options->getPage());
        self::assertSame(10, $options->getMaxPages());
        self::assertSame(20, $options->getLimit());
        self::assertSame('created_at', $options->getOrder());
        self::assertSame('DESC', $options->getOrderDir());
        self::assertSame(['active' => 'true'], $options->getCriteria());
    }
}

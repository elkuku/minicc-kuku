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

        $this->assertSame(0, $options->getPage());
        $this->assertSame(0, $options->getMaxPages());
        $this->assertSame(10, $options->getLimit());
        $this->assertSame('id', $options->getOrder());
        $this->assertSame('ASC', $options->getOrderDir());
        $this->assertSame([], $options->getCriteria());
    }

    public function testSetPage(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setPage(5);

        $this->assertSame($options, $result);
        $this->assertSame(5, $options->getPage());
    }

    public function testSetMaxPages(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setMaxPages(20);

        $this->assertSame($options, $result);
        $this->assertSame(20, $options->getMaxPages());
    }

    public function testSetLimit(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setLimit(25);

        $this->assertSame($options, $result);
        $this->assertSame(25, $options->getLimit());
    }

    public function testSetOrder(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setOrder('name');

        $this->assertSame($options, $result);
        $this->assertSame('name', $options->getOrder());
    }

    public function testSetOrderDirAsc(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setOrderDir('ASC');

        $this->assertSame($options, $result);
        $this->assertSame('ASC', $options->getOrderDir());
    }

    public function testSetOrderDirDesc(): void
    {
        $options = new PaginatorOptions();

        $result = $options->setOrderDir('DESC');

        $this->assertSame($options, $result);
        $this->assertSame('DESC', $options->getOrderDir());
    }

    public function testSetOrderDirCaseInsensitive(): void
    {
        $options = new PaginatorOptions();

        $options->setOrderDir('desc');

        $this->assertSame('desc', $options->getOrderDir());
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

        $this->assertSame($options, $result);
        $this->assertSame($criteria, $options->getCriteria());
    }

    public function testSearchCriteriaExistingKey(): void
    {
        $options = new PaginatorOptions();
        $options->setCriteria(['name' => 'John', 'status' => 'active']);

        $this->assertSame('John', $options->searchCriteria('name'));
        $this->assertSame('active', $options->searchCriteria('status'));
    }

    public function testSearchCriteriaNonExistingKey(): void
    {
        $options = new PaginatorOptions();
        $options->setCriteria(['name' => 'John']);

        $this->assertSame('', $options->searchCriteria('nonexistent'));
    }

    public function testSearchCriteriaEmptyCriteria(): void
    {
        $options = new PaginatorOptions();

        $this->assertSame('', $options->searchCriteria('anykey'));
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

        $this->assertSame($options, $result);
        $this->assertSame(1, $options->getPage());
        $this->assertSame(10, $options->getMaxPages());
        $this->assertSame(20, $options->getLimit());
        $this->assertSame('created_at', $options->getOrder());
        $this->assertSame('DESC', $options->getOrderDir());
        $this->assertSame(['active' => 'true'], $options->getCriteria());
    }
}

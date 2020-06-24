<?php

namespace App\Helper\Paginator;

class PaginatorOptions
{
    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var int
     */
    private $maxPages = 0;

    /**
     * @var int
     */
    private $limit = 10;

    /**
     * @var string
     */
    private $order = 'id';

    /**
     * @var string
     */
    private $orderDir = 'ASC';

    /**
     * @var array
     */
    private $criteria = [];

    public function setPage(int $page): PaginatorOptions
    {
        $this->page = $page;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setOrder(string $order): PaginatorOptions
    {
        $this->order = $order;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrderDir(string $orderDir): PaginatorOptions
    {
        $dirs = ['ASC', 'DESC'];
        $dir = strtoupper($orderDir);

        if (false === \in_array($dir, $dirs, true)) {
            throw new \UnexpectedValueException(sprintf('Order dir must be %s', implode(', ', $dirs)));
        }

        $this->orderDir = $orderDir;

        return $this;
    }

    public function getOrderDir(): string
    {
        return $this->orderDir;
    }

    public function setCriteria(array $criteria): PaginatorOptions
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function setMaxPages(int $maxPages): PaginatorOptions
    {
        $this->maxPages = $maxPages;

        return $this;
    }

    public function getMaxPages(): int
    {
        return $this->maxPages;
    }

    public function setLimit(int $limit): PaginatorOptions
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function searchCriteria(string $name): string
    {
        return array_key_exists($name, $this->criteria) ? $this->criteria[$name]
            : '';
    }
}

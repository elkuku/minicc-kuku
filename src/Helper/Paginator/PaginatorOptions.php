<?php

namespace App\Helper\Paginator;

use function in_array;
use UnexpectedValueException;

class PaginatorOptions
{
    /**
     * @var int
     */
    private int $page = 0;

    /**
     * @var int
     */
    private int $maxPages = 0;

    /**
     * @var int
     */
    private int $limit = 10;

    /**
     * @var string
     */
    private string $order = 'id';

    /**
     * @var string
     */
    private string $orderDir = 'ASC';

    /**
     * @var array<string>
     */
    private array $criteria = [];

    public function setPage(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setOrder(string $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrderDir(string $orderDir): static
    {
        $dirs = ['ASC', 'DESC'];
        $dir = strtoupper($orderDir);

        if (false === in_array($dir, $dirs, true)) {
            throw new UnexpectedValueException(
                sprintf('Order dir must be %s', implode(', ', $dirs))
            );
        }

        $this->orderDir = $orderDir;

        return $this;
    }

    public function getOrderDir(): string
    {
        return $this->orderDir;
    }

    /**
     * @param array<string> $criteria
     */
    public function setCriteria(array $criteria): static
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function setMaxPages(int $maxPages): static
    {
        $this->maxPages = $maxPages;

        return $this;
    }

    public function getMaxPages(): int
    {
        return $this->maxPages;
    }

    public function setLimit(int $limit): static
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

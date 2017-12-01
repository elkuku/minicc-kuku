<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Helper\Paginator;

/**
 * Class PaginatorOptions
 */
class PaginatorOptions
{
    private $page = 0;

    private $maxPages = 0;

    private $limit = 10;

    private $order = 'id';

    private $orderDir = 'ASC';

    private $criteria = [];

    /**
     * @param int $page
     *
     * @return PaginatorOptions
     */
    public function setPage($page)
    {
        $this->page = (int) $page;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param string $order
     *
     * @return PaginatorOptions
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $orderDir
     *
     * @return PaginatorOptions
     */
    public function setOrderDir($orderDir)
    {
        $dirs = ['ASC', 'DESC'];
        $dir  = strtoupper($orderDir);

        if (false == in_array($dir, $dirs)) {
            throw new \UnexpectedValueException(sprintf('Order dir must be %s', implode(', ', $dirs)));
        }

        $this->orderDir = $orderDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderDir()
    {
        return $this->orderDir;
    }

    /**
     * @param array $criteria
     *
     * @return PaginatorOptions
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * @return array
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param int $maxPages
     *
     * @return PaginatorOptions
     */
    public function setMaxPages($maxPages)
    {
        $this->maxPages = (int) $maxPages;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxPages()
    {
        return $this->maxPages;
    }

    /**
     * @param int $limit
     *
     * @return PaginatorOptions
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param string $name
     *
     * @return string Criteria value or empty string
     */
    public function searchCriteria($name)
    {
        return array_key_exists($name, $this->criteria) ? $this->criteria[$name] : '';
    }
}

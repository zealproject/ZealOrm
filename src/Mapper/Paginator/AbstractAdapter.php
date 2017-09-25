<?php

namespace Zeal\Orm\Mapper\Paginator;

use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

abstract class AbstractAdapter implements AdapterInterface
{
    protected $mapper;

    protected $query;

    protected $rowCount;


    public function __construct($mapper, $query)
    {
        $this->mapper = $mapper;
        $this->query = $query;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  int $offset           Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $query = clone $this->query;
        $query->offset($offset);
        $query->limit($itemCountPerPage);

        return $this->mapper->fetchAll($query);
    }
}

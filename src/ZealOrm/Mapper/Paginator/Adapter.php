<?php

namespace ZealOrm\Mapper\Paginator;

use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class Adapter implements AdapterInterface
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

    /**
     * Returns the total number of rows in the result set.
     *
     * @return int
     */
    public function count()
    {
        if ($this->rowCount === null) {
            $select = clone $this->query;
            $select->reset(Select::LIMIT);
            $select->reset(Select::OFFSET);
            $select->reset(Select::ORDER);

            // this is how you do this with a sub select
            /*
            $countSelect = new Select;
            $countSelect->columns(array('c' => new Expression('COUNT(1)')));
            $countSelect->from(array('original_select' => $select));
            */

            // ...but resetting the columns is much faster
            $select->reset(Select::COLUMNS);
            $select->columns(array('c' => new Expression('COUNT(1)')));

            $sql = new Sql($this->mapper->getAdapter()->getDb());
            $statement = $sql->prepareStatementForSqlObject($select);
            $result    = $statement->execute();
            $row       = $result->current();

            $this->rowCount = $row['c'];
        }

        return $this->rowCount;
    }
}

<?php

namespace Zeal\Orm;

use IteratorAggregate;
use ArrayIterator;
use Countable;

class Collection implements IteratorAggregate, Countable
{
    protected $adapter;

    protected $query;

    protected $className;

    protected $data;

    public function __construct($adapter, $query, $className)
    {
        $this->adapter = $adapter;
        $this->query = $query;
        $this->className = $className;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function loadData()
    {
        $data = $this->adapter->fetchAll($this->query);
        $className = $this->className;

        if ($data) {
            $results = array();

            foreach ($data as $row) {
                $object = new $className();
                $object->getHydrator()->hydrate($row, $object);

                $results[] = $object;
            }

            $this->data = $results;

        } else {
            $this->data = array();
        }
    }

    public function getData()
    {
        if ($this->data === null) {
            $this->loadData();
        }

        return $this->data;
    }

    public function getFirstRow()
    {
        if ($this->data === null) {
            $this->loadData();
        }

        return isset($this->data[0]) ? $this->data[0] : null;
    }

    public function getIterator()
    {
        if ($this->data === null) {
            $this->loadData();
        }

        return new ArrayIterator($this->data);
    }

    public function first()
    {
        $this->query->limit(1);

        return $this;
    }

    public function order($order)
    {
        $this->query->order($order);

        return $this;
    }

    /**
     * Returns the number of items in the collection (part of Countable)
     *
     * @return integer
     */
    public function count()
    {
        if ($this->data === null) {
            $this->loadData();
        }

        return count($this->data);
    }
}

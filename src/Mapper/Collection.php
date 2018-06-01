<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Mapper;

use ArrayIterator;
use Zeal\Orm\CollectionInterface;
use Zeal\Orm\Mapper\MapperInterface;
use Zeal\Orm\Adapter\Query\QueryInterface;

class Collection implements CollectionInterface
{
    /**
     * @var MapperInterface
     */
    protected $mapper;

    /**
     * @var QueryInterface
     */
    protected $query;

    /**
     * @var array|null
     */
    protected $data;

    public function __construct(MapperInterface $mapper, QueryInterface $query)
    {
        $this->mapper = $mapper;
        $this->query = $query;
    }

    /**
     * Returns the query object used by this collection.
     *
     * @return QueryInterface
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Loads the data for the collection
     *
     * @return array
     */
    protected function loadData()
    {
        $this->data = $this->mapper->fetchObjects($this->query);
    }

    /**
     * Returns the data for this collection, loading it if required.
     *
     * @return array|null
     */
    public function getData()
    {
        if ($this->data === null) {
            $this->loadData();
        }

        return $this->data;
    }

    /**
     * Returns the number of items in the collection
     *
     * @return integer
     */
    public function count()
    {
        return $this->mapper->getAdapter()->count($this->query);
    }

    public function first()
    {
        $this->getData();
        if (count($this->data) > 0) {
            return $this->data[0];
        }

        return null;
    }

    /**
     * Returns an iterator using the collection data.
     *
     * Required for IteratorAggregate. This allows templates to loop through the collection
     * with the data loaded on demand.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getData());
    }
}

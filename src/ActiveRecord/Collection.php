<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\ActiveRecord;

use ArrayIterator;
use Zeal\Orm\CollectionInterface;
use Zeal\Orm\Adapter\AdapterInterface;
use Zeal\Orm\Adapter\Query\QueryInterface;

class Collection implements CollectionInterface
{
    protected $adapter;

    /**
     * @var QueryInterface
     */
    protected $query;

    protected $className;

    /**
     * @var array|null
     */
    protected $data;

    public function __construct(AdapterInterface $adapter, QueryInterface $query, $className)
    {
        $this->adapter = $adapter;
        $this->query = $query;
        $this->className = $className;
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
        $rawData = $this->adapter->fetchAll($this->query);
        $instance = new $this->className();
        $hydrator = $instance->getHydrator();

        $this->data = [];
        foreach ($rawData as $row) {
            $this->data[] = $hydrator->hydrate($row, clone $instance);
        }
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
        $data = $this->getData();
        if (count($data) > 0) {
            return $data[0];
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

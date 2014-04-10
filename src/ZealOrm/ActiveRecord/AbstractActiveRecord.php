<?php

/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\ActiveRecord;

use ZealOrm\Model\AbstractModel;
use ZealOrm\Orm;
use ZealOrm\Model\Hydrator;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZealOrm\ActiveRecord\ActiveRecordInterface;
use ZealOrm\Collection;

abstract class AbstractActiveRecord extends AbstractModel implements ActiveRecordInterface
{
    /**
     * The adapter used by this ActiveRecord object
     *
     * @var ZealOrm\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * [$defaultAdapterOptions description]
     * @var array|null
     */
    protected static $defaultAdapterOptions;

    /**
     * [$hydrator description]
     * @var [type]
     */
    protected $hydrator;

    /**
     * The model's fields
     *
     * @var array
     */
    protected $fields = array();


    /**
     * Setter for the adapter
     *
     * @param ZealOrm\Adapter\AdapterInterface $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Returns an instance of the adapter, creating it if required
     *
     * @return ZealOrm\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = static::getStaticAdapter();
        }

        return $this->adapter;
    }

    /**
     * Returns an instance of the adapter with the default options
     *
     * @return ZealOrm\Adapter\AdapterInterface
     */
    public static function getStaticAdapter()
    {
        $adapter = Orm::getDefaultAdapter();

        $adapter->setOptions(static::getDefaultAdapterOptions());

        return $adapter;
    }

    /**
     * Setter for the model's fields array
     *
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Returns the model's fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Setter for the hydrator
     *
     * @param ZealOrm\Model\Hydrator $hydrator
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Returns the hydrator
     *
     * @return ZealOrm\Model\Hydrator
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new Hydrator();
            $this->hydrator->setFields($this->getFields());
        }

        return $this->hydrator;
    }

    /**
     * Returns a collection object with the adapter and query object populated
     *
     * @return Collection
     */
    protected static function buildCollection()
    {
        $adapter = static::getStaticAdapter();

        $query = $adapter->buildQuery();

        $collection = new Collection(static::getStaticAdapter(), $query, get_called_class());

        return $collection;
    }
}

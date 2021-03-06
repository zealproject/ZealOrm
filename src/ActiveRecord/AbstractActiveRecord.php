<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\ActiveRecord;

use Zeal\Orm\Model\AbstractModel;
use Zeal\Orm\Orm;
use Zeal\Orm\Hydrator\ModelHydrator;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\HydratorAwareInterface;
use Zeal\Orm\ActiveRecord\ActiveRecordInterface;
use Zeal\Orm\ActiveRecord\Collection;

abstract class AbstractActiveRecord extends AbstractModel implements ActiveRecordInterface, HydratorAwareInterface
{
    /**
     * The adapter used by this ActiveRecord object
     *
     * @var Zeal\Orm\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * [$defaultAdapterOptions description]
     * @var array|null
     */
    protected static $defaultAdapterOptions;

    /**
     * [$hydrator description]
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * The model's fields
     *
     * @var array
     */
    protected static $fields = [];


    /**
     * Setter for the adapter
     *
     * @param Zeal\Orm\Adapter\AdapterInterface $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Returns an instance of the adapter, creating it if required
     *
     * @return Zeal\Orm\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = static::getStaticAdapter();
        }

        return $this->adapter;
    }

    /**
     * Setter for the model's fields array
     *
     * @param array $fields
     */
    public function setFields($fields)
    {
        static::$fields = $fields;

        return $this;
    }

    /**
     * Returns the model's fields
     *
     * @return array
     */
    public function getFields()
    {
        return static::$fields;
    }

    /**
     * Setter for the hydrator
     *
     * @param Zeal\Orm\Model\Hydrator $hydrator
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Returns the hydrator
     *
     * @return Zeal\Orm\Hydrator\Model
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new ModelHydrator($this->getFields());
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

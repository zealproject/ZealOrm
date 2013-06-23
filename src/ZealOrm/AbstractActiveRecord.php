<?php

/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm;

use ZealOrm\Model\AbstractModel;
use ZealOrm\Orm;
use ZealOrm\Model\Hydrator;

abstract class AbstractActiveRecord extends AbstractModel
{
    /**
     * The adapter used by this ActiveRecord object
     *
     * @var ZealOrm\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * The model's fields
     *
     * @var array
     */
    protected $fields = array();

    /**
     * [$hydrator description]
     * @var [type]
     */
    protected $hydrator;


    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = Orm::getDefaultAdapter();

            $this->adapter->setOptions(array(
                'tableName' => 'tokens'
            ));
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
     * @param [type] $hydrator [description]
     */
    public function setHydrator($hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Returns the hydrator
     *
     * @return ZealOrm\Model\Hydrator [description]
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new Hydrator();
            $this->hydrator->setFields($this->getFields());
        }

        return $this->hydrator;
    }

    public static function find($id)
    {

    }

    public static function create(array $data)
    {
        $object = new static();
        $object->getHydrator()->hydrate($data, $object);

        $data = $object->getHydrator()->extract($object);

        if ($object->getAdapter()->create($data)) {
            return $object;
        }

        return false;
    }

    public static function update()
    {

    }

    public static function save()
    {

    }

    public static function delete()
    {

    }
}

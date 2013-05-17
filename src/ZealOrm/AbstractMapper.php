<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm;

use ZealOrm\Mapper\Adapter\Zend\Db;
use ZealOrm\Model\Hydrator;

abstract class AbstractMapper
{
    protected $adapter;

    protected $className;

    protected $adapterOptions = array();

    protected $fields = array();

    protected $hydrator;


    public function getClassName()
    {
        if (!$this->className) {
            // attempt to work out the class name from the mapper class name
            $mapperClass = get_class($this);
            if (substr($mapperClass, -6) == 'Mapper') {
                $className = substr($mapperClass, 0, -6);
                if (class_exists($className)) {
                    $this->className = $className;

                } else {
                    throw new Exception('Unable to determine class name in apper '.$mapperClass);
                }
            }
        }

        return $this->className;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getAdapterOptions()
    {
        return $this->adapterOptions;
    }

    public function setHydrator($hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new Hydrator();
            $this->hydrator->setFields($this->getFields());
        }

        return $this->hydrator;
    }

    public function buildQuery()
    {
        return $this->getAdapter()->buildQuery();
    }

    /**
     * Converts a data array into a object
     *
     * @param array $data
     * @param boolean $guard
     * @return object
     */
    public function arrayToObject(array $data, $guard = true)
    {
        $className = $this->getClassName();
        $object = new $className();

        $this->getHydrator()->hydrate((array)$data, $object);

        return $object;
    }

    /**
     * Converts the result of an adapter query into an object
     *
     * This function is called on any data returned by the mapper's adapter. In most
     * cases this data will be in an array-type format, and so by default this calls
     * arrayToObject, but the function exists to allow custom functionality at the mapper
     * level for any adapters that return other data structures.
     *
     * @param mixed $result
     * @return object
     */
    public function resultToObject($result, $guard = true)
    {
        return $this->arrayToObject($result, $guard);
    }

    public function find($id, $query = null)
    {
        // if ($this->isCached($id)) {
        //     return $this->getCached($id);
        // }

        $data = $this->getAdapter()->find($id, $query);
        if ($data) {
            $object = $this->resultToObject($data, false);

            //$this->cache($object, $id);

            return $object;
        }

        return false;
    }

    public function fetchAll($query = null)
    {
        if (!$query) {
            $query = $this->buildQuery();
        }

        $data = $this->getAdapter()->fetchAll($query);
        if ($data) {
            $results = array();
            foreach ($data as $result) {
                $results[] = $this->resultToObject($result, false);
            }

            return $results;
        }

        return array();
    }

    public function fetchObject($query)
    {
        $data = $this->getAdapter()->fetchObject($query);
        if ($data) {
            return $this->resultToObject($data, false);
        }

        return false;
    }

    public function prepare($object)
    {

    }

    public function create($object)
    {
        $this->prepare($object);

        return $this->getAdapter()->create($object);
    }

    public function initAssociation($type, $options = array())
    {
        $association = new \ZealOrm\Mapper\Adapter\Zend\Db\Association\HasAndBelongsToMany($options);

        return $association;
    }

    public function buildQueryForAssociation($association)
    {
        $query = $this->buildQuery();

        $query = $this->getAdapter()->populateAssociationQuery($query, $association);

var_dump($query);exit;
        return $query;
    }
}
<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm;

use ZealOrm\Orm;
use ZealOrm\Model\Hydrator;

abstract class AbstractModel extends Hydrator
{
    /**
     * @var boolean
     */
    protected $dirty;


    /**
     * Magic method for returning model data
     *
     * @param string $var
     * @return mixed
     */
    public function __get($var)
    {
        $getMethodName = 'get'.ucfirst($var);
        if (method_exists($this, $getMethodName)) {
            // use the get method
            return $this->$getMethodName();

        } else if (property_exists($this, $var)) {
            // return the value
            return $this->$var;

        } else {
            // exception?
        }
    }
    /**
     * Magic method for setting model data
     *
     * @param string $var
     * @param mixed $value
     * @return void
     */
    public function __set($var, $value)
    {
        $setMethodName = 'set'.ucfirst($var);
        if (method_exists($this, $setMethodName)) {
            if (!$this->dirty) {
                $this->dirty = true;
            }

            // use the set method
            return $this->$setMethodName($value);

        } else if (property_exists($this, $var)) {
            if (!$this->dirty && $this->$var != $value) {
                $this->dirty = true;
            }

            $this->$var = $value;
        }
    }
    public function populate(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getArrayCopy()
    {
        $mapper = Orm::getMapper($this);
        $fields = $mapper->getFields();

        $data = array();
        foreach ($fields as $field => $fieldType) {
            $data[$field] = isset($this->$field) ? $this->$field : null;
        }

        return $data;
    }
}
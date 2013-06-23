<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Model;

use ZealOrm\Orm;
use ZealOrm\Model\Association\AssociationInterface;

abstract class AbstractModel
{
    /**
     * @var boolean
     */
    protected $dirty;

    /**
     * @var null|array
     */
    protected $associations;


    public function __construct($data = null)
    {
        $this->init();
    }

    public function init()
    {

    }

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
            if ($this->$var === null && $this->isAssociation($var)) {
                $this->$var = $this->getAssociation($var)->loadData();
            }

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
        // FIXME
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Checks whether the shortname supplied is an association
     *
     * @param string $associationShortname
     * @return boolean
     */
    public function isAssociation($shortname)
    {
        return isset($this->associations[$shortname]);
    }

    public function getAssociation($shortname)
    {
        return $this->associations[$shortname];
    }

    /**
     * Initialises an association
     *
     * Creates an instance of the appropriate association class based on the
     * supplied type and stores this model.
     *
     * @param $type
     * @param $shortname
     * @param $options
     * @return void
     */
    protected function initAssociation($type, $shortname, $options = array())
    {
        if (!$this->associations) {
            $this->associations = array();
        }

        // make sure it doesn't already exist
        if (array_key_exists($shortname, $this->associations)) {
            throw new \Exception('Association \''.htmlspecialchars($shortname).'\' already exists');
        }

        // get the target mapper for the association
        if (isset($options['mapper'])) {
            if (!($options['mapper'] instanceof Zeal_MapperInterface)) {
                throw new \Exception('Mapper specified for association \''.htmlspecialchars($shortname).'\' must implement Zeal_MapperInterface');
            }

            $targetMapper = $options['mapper'];

        } else {
            if (empty($options['className'])) {
                // TODO: inflection based on the name of the association
                throw new \Exception('No class name specified for association \''.htmlspecialchars($shortname).'\' in model \''.get_class($this).'\'');

            } else if (class_exists($options['className'])) {
                $targetMapper = \ZealOrm\Orm::getMapper($options['className']);

            } else {
                throw new \Exception('Invalid class name of \''.htmlspecialchars($options['className']).'\' specified for association \''.htmlspecialchars($shortname).'\' in model \''.get_class($this).'\'');
            }
        }

        $association = $targetMapper->initAssociation($type, $options);

        // add some things the association might need
        $association->setShortname($shortname)
                    ->setSource($this)
                    ->setTargetClassName($options['className']);

        //$association->init();

        // store the association in the model
        $this->associations[$shortname] = $association;
    }

    /**
     * Create a 'belongs to' association
     *
     * @param $shortname
     * @return void
     */
    public function belongsTo($shortname, $options = array())
    {
        $this->initAssociation(AssociationInterface::BELONGS_TO, $shortname, $options);
    }

    /**
     * Create a 'has one' association
     *
     * @param $shortname
     * @return void
     */
    public function hasOne($shortname, $options = array())
    {
        $this->initAssociation(AssociationInterface::HAS_ONE, $shortname, $options);
    }

    /**
     * Create a 'has many' association
     *
     * @param $shortname
     * @return void
     */
    public function hasMany($shortname, $options = array())
    {
        $this->initAssociation(AssociationInterface::HAS_MANY, $shortname, $options);
    }

    /**
     * Create a 'has and belongs to many' association
     *
     * @param $shortname
     * @return void
     */
    public function hasAndBelongsToMany($shortname, $options = array())
    {
        $this->initAssociation(AssociationInterface::HAS_AND_BELONGS_TO_MANY, $shortname, $options);
    }
}

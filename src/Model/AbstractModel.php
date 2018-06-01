<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Model;

use Zeal\Orm\Orm;
use Zeal\Orm\Association\AssociationInterface;
use Zeal\Orm\Mapper\MapperInterface;
use Zeal\Orm\Hydrator\ModelHydrator;
use Zend\Hydrator\HydratorInterface;
use Serializable;

use Zeal\Orm\Model\Hydrator;

abstract class AbstractModel implements Serializable
{
    /**
     * @var boolean
     */
    protected $dirty;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var null|array
     */
    protected $associations;

    /**
     * @var null|array
     */
    protected $associationPropertyListeners;


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

        } elseif (property_exists($this, $var)) {
            if ($this->$var === null && $this->isAssociation($var)) {
                $this->$var = $this->getAssociation($var)->buildCollection($this);
            } elseif ($this->isAssociationPropertyListener($var)) {
                $mapToAssociation = $this->associationPropertyListeners[$var];

                $association = $this->getAssociation($mapToAssociation);

                return $association->getListenerProperty($var);
            }

            // return the value
            return $this->$var;

        } else {
            throw new \Exception("Attempted to access non-existent property '".htmlspecialchars($var)."' on ".get_class($this));
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

        } elseif (property_exists($this, $var)) {
            if ($this->isAssociation($var)) {
                // TODO

            } elseif ($this->isAssociationPropertyListener($var)) {
                $mapToAssociation = $this->associationPropertyListeners[$var];

                $association = $this->getAssociation($mapToAssociation);
                $association->setListenerProperty($var, $value);
                $association->setDirty(true);

            } else {
                // just a normal object property
                if (!$this->dirty && $this->$var != $value) {
                    $this->dirty = true;
                }

                $this->$var = $value;
            }
        }
    }

    /**
     * Setter for the hydrator
     *
     * @param HydratorInterface $hydrator
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Returns the hydrator
     *
     * @return Zeal\Orm\Model\Hydrator
     */
    public function getHydrator()
    {
        if ($this->hydrator === null) {
            throw new \Exception('No hydrator defined on '.get_class($this));
        }
        return $this->hydrator;
    }

    public function hydrate($data)
    {
        $this->getHydrator()->hydrate($data, $this);
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

    /**
     * Checks whether the var name supplied is an association listener
     *
     * @param string $var
     * @return boolean
     */
    public function isAssociationPropertyListener($var)
    {
        return isset($this->associationPropertyListeners[$var]);
    }

    /**
     * Getter for association
     *
     * @param  string $shortname
     * @return ZealOrm\Model\Association\AssociationInterface
     */
    public function getAssociation($shortname)
    {
        // ensure the association has a source
        // if (!$this->associations[$shortname]->hasSource()) {
        //     $this->associations[$shortname]->setSource($this);
        // }

        return $this->associations[$shortname];
    }

    /**
     * Returns all associations for this model
     *
     * @return array
     */
    public function getAssociations()
    {
        if ($this->associations === null) {
            return [];
        }

        return $this->associations;
    }

    /**
     * Stores an association object in the model
     *
     * @param string               $shortname
     * @param AssociationInterface $association
     */
    public function addAssociation($shortname, AssociationInterface $association)
    {
        $this->associations[$shortname] = $association;
    }

    /**
     * [addAssociationPropertyListener description]
     * @param [type] $var                  [description]
     * @param [type] $associationShortname [description]
     */
    public function addAssociationPropertyListener($var, $associationShortname)
    {
        // TODO validate association here
        // TODO ensure var isn't already set

        $this->associationPropertyListeners[$var] = $associationShortname;

        return $this;
    }

    /**
     * Returns an array of associations with unsaved data
     *
     * @return array
     */
    public function getAssociationsWithUnsavedData()
    {
        $associations = $this->getAssociations();
        if (!$associations) {
            return [];
        }

        $unsavedAssociations = [];
        foreach ($associations as $shortname => $association) {
            if ($association->isDirty()) {
                $unsavedAssociations[$shortname] = $association;
            }
        }

        return $unsavedAssociations;
    }

    public function toArray()
    {
        return $this->getHydrator()->extract($this);
    }

    /**
     * Serialize an object (for Serializeable)
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     * Unserialize the model (for Serializeable)
     *
     * @param  array $data
     * @return void
     */
    public function unserialize($data)
    {
        $this->getHydrator()->hydrate($data, $this);
    }

    /**
     * Returns dirty state
     *
     * @return bool
     */
    public function isDirty()
    {
        return $this->dirty;
    }
}

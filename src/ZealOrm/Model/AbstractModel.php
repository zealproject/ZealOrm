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
use ZealOrm\Mapper\MapperInterface;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Serializable;

use ZealOrm\Model\Hydrator;


abstract class AbstractModel implements HydratorAwareInterface, EventManagerAwareInterface, Serializable
{
    /**
     * @var boolean
     */
    protected $dirty;

    /**
     * @var null|array
     */
    protected $associations;

    /**
     * @var null|array
     */
    protected $associationPropertyListeners;

    /**
     * @var Hydrator
     */
    protected $hydrator;

    /**
     * @var EventManager
     */
    protected $events;


    public function __construct($data = null)
    {
        $this->init();

        if ($data) {
            $this->populate($data);
        }
    }

    /**
     * Can be overridden for custom functionality. Called by constructor.
     *
     * @return void
     */
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
            } else if ($this->isAssociationPropertyListener($var)) {
                $mapToAssociation = $this->associationPropertyListeners[$var];

                $association = $this->getAssociation($mapToAssociation);

                return $association->getListenerProperty($var);
            }

            // return the value
            return $this->$var;

        } else {
            throw new \Exception("Attempt to access non-existent property '".htmlspecialchars($var)."' on ".get_class($this));
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
            if ($this->isAssociation($var)) {
                // TODO

            } else if ($this->isAssociationPropertyListener($var)) {
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
     * Set hydrator
     *
     * @param  HydratorInterface $hydrator
     * @return HydratorAwareInterface
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Retrieve hydrator
     *
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new Hydrator();
            //$hydrator->setFields($mapper->getFields()); FIXME
        }

        return $this->hydrator;
    }

    /**
     * Setter for the event manager
     *
     * @param EventManagerInterface $events
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            get_class($this)
        ));

        $this->events = $events;

        return $this;
    }

    /**
     * Getter for event manager. Creates instance on demand
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * Hydrate some data into the object (shortcut for the hydrator)
     *
     * @param  array  $data
     * @return void
     */
    public function populate(array $data)
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
        if (!$this->associations[$shortname]->hasSource()) {
            $this->associations[$shortname]->setSource($this);
        }

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
            return array();
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
            return array();
        }

        $unsavedAssociations = array();
        foreach ($associations as $shortname => $association) {
            if ($association->isDirty()) {
                $unsavedAssociations[$shortname] = $association;
            }
        }

        return $unsavedAssociations;
    }

    /**
     * Serialize an object (for Serializeable)
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->getHydrator()->extract($this));
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
}

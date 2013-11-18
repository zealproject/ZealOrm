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

abstract class AbstractModel implements HydratorAwareInterface, EventManagerAwareInterface
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


    public function populate(array $data)
    {
        // FIXME: somehow call the hydrator here?
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
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
     * Builds an association object
     *
     * @param $type
     * @param $shortname
     * @param $options
     * @return void
     */
    /*protected function buildAssociation($type, $shortname, $options = array())
    {
        // get the target mapper for the association
        if (isset($options['mapper'])) {
            if (!($options['mapper'] instanceof MapperInterface)) {
                throw new \Exception('Mapper specified for association \''.htmlspecialchars($shortname).'\' must implement ZealOrm\Mapper\MapperInterface');
            }

            $targetMapper = $options['mapper'];

        } else {
            if (empty($options['className'])) {
                // TODO: inflection based on the name of the association
                throw new \Exception('No class name specified for association \''.htmlspecialchars($shortname).'\' in model \''.get_class($this).'\'');

            } else if (class_exists($options['className'])) {
                $targetMapper = Orm::getMapper($options['className']);

            } else {
                throw new \Exception('Invalid class name of \''.htmlspecialchars($options['className']).'\' specified for association \''.htmlspecialchars($shortname).'\' in model \''.get_class($this).'\'');
            }
        }

        $association = $targetMapper->buildAssociation($type, $options);

        // add some things the association might need
        $association->setShortname($shortname)
                    ->setSource($this)
                    ->setTargetClassName($options['className']);

        return $association;
    }*/

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
    /*protected function initAssociation($type, $shortname, $options = array())
    {
        if (!$this->associations) {
            $this->associations = array();
        }

        // make sure it doesn't already exist
        if (array_key_exists($shortname, $this->associations)) {
            throw new \Exception('Association \''.htmlspecialchars($shortname).'\' already exists');
        }

        $association = $this->buildAssociation($type, $shortname, $options);

        // store the association in the model
        $this->associations[$shortname] = $association;
    }*/

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
}

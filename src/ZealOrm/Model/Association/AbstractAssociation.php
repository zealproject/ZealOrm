<?php

namespace ZealOrm\Model\Association;

use ZealOrm\Model\Association\AssociationInterface;
//use ZealOrm\Model\Association\Data\Container as DataContainer;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use ZealOrm\Orm;

abstract class AbstractAssociation implements AssociationInterface, EventManagerAwareInterface
{
    protected $type;

    protected $shortname;

    protected $targetMapper;

    protected $targetClassName;

    protected $source;

    protected $sourceMapper;

    protected $options;

    /**
     * @var boolean
     */
    protected $dirty = false;

    protected $events;


    public function __construct(array $options = array())
    {
        $this->options = $options;
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

    public function setTargetMapper($mapper)
    {
        $this->targetMapper = $mapper;
    }

    /**
     * Returns the mapper class for the target of this association
     *
     * @return ZealOrm\Mapper\MapperInterface
     */
    public function getTargetMapper()
    {
        if (!$this->targetMapper) {
            if ($this->getOption('polymorphic', false)) {
                $this->targetMapper = Orm::getMapper($this->getPolymorphicType());

            } else {
                $this->targetMapper = Orm::getMapper($this->getTargetClassName());
            }
        }

        return $this->targetMapper;
    }

    public function setSource($sourceModel)
    {
        $this->source = $sourceModel;

        return $this;
    }

    /**
     * Returns true if the association has a source object
     *
     * @return boolean
     */
    public function hasSource()
    {
        return $this->source !== null;
    }

    /**
     * Returns the source model for this association
     *
     * @return object
     */
    public function getSource()
    {
        return $this->source;
    }

    public function setSourceMapper($mapper)
    {
        $this->sourceMapper = $mapper;
    }

    public function getSourceMapper()
    {
        if (!$this->sourceMapper) {
            $source = $this->getSource();
            if (!$source) {
                throw new \Exception('Unable to load source mapper as no source exists');
            }

            $this->sourceMapper = Orm::getMapper(get_class($source));
        }

        return $this->sourceMapper;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setShortname($shortname)
    {
        $this->shortname = $shortname;

        return $this;
    }

    public function getShortname()
    {
        return $this->shortname;
    }

    /**
     * Returns true if the supplied option exists
     *
     * @param string $key
     * @return boolean
     */
    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Retreives the option with the specified key,
     *
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        } else {
            return $default;
        }
    }

    /*
     * Sets an option
     *
     * @param string $key
     * @param mixed $value
     * @return AbstractAssociation
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Returns the options array
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setTargetClassName($targetClassName)
    {
        $this->targetClassName = $targetClassName;
    }

    public function getTargetClassName()
    {
        return $this->targetClassName;
    }

    /**
     * Returns the value for the specified column
     *
     * @param  object $model
     * @param  string $column
     * @return mixed
     */
    public function getColumnValue($model, $column)
    {
        if ($column == 'class') {
            return get_class($model);
        }

        return $model->$column;
    }

    public function setListenerProperty($var, $value)
    {

    }

    public function getListenerProperty($var)
    {

    }

    /**
     * Getter for the 'dirty' property
     *
     * @return boolean
     */
    public function isDirty()
    {
        return $this->dirty;
    }

    /**
     * Setter for the 'dirty' property
     *
     * @param boolean $value
     */
    public function setDirty($value)
    {
        $this->dirty = $value;

        return $this;
    }

    public function getPolymorphicType()
    {
        $shortname = $this->getShortname();

        return $this->getSource()->{$shortname.'_type'};
    }

    public function getPolymorphicIdColumn()
    {
        $shortname = $this->getShortname();

        return $shortname.'_id';
    }
}

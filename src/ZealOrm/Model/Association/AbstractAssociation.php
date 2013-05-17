<?php

namespace ZealOrm\Model\Association;

use ZealOrm\Model\Association\AssociationInterface;

abstract class AbstractAssociation implements AssociationInterface
{
    protected $type;

    protected $shortname;

    protected $targetMapper;

    protected $targetClassName;

    protected $source;

    protected $sourceMapper;

    protected $options;


    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function setTargetMapper($mapper)
    {
        $this->targetMapper = $mapper;
    }

    public function getTargetMapper()
    {
        if (!$this->targetMapper) {
            $this->targetMapper = \ZealOrm\Orm::getMapper($this->getTargetClassName());
        }

        return $this->targetMapper;
    }

    public function setSource($sourceModel)
    {
        $this->source = $sourceModel;

        return $this;
    }

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
            $this->sourceMapper = \ZealOrm\Orm::getMapper(get_class($this->source));
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
     * @return Zeal_Model_AssociationAbstract
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

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

    public function loadData()
    {
        $query = $this->buildQuery();

        return $this->getTargetMapper()->fetchAll($query);
    }
}
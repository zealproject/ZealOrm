<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Adapter;

use ZealOrm\Adapter\AdapterInterface;
use ZealOrm\Model\Association\AssociationInterface;
use ZealOrm\Model\Association;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * The options array
     *
     * @var array
     */
    protected $options = array();

    /**
     * Sets the adapter options
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Gets a specific option
     *
     * @param  string $key    The option to return
     * @param  mixed $default The default value, returned if the option isn't set
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        if (!array_key_exists($key, $this->options)) {
            return $default;
        }

        return $this->options[$key];
    }

    public function buildAssociation($type, $options)
    {
        if ($type == AssociationInterface::BELONGS_TO) {
            $association = new Association\BelongsTo($options);

        } else if ($type == AssociationInterface::HAS_ONE) {
            $association = new Association\HasOne($options);

        } else if ($type == AssociationInterface::HAS_MANY) {
            $association = new Association\HasMany($options);

        } else if ($type == AssociationInterface::HAS_AND_BELONGS_TO_MANY) {
            $association = new Association\HasAndBelongsToMany($options);

        } else {
            throw new Exception('Attempted to initialise unknown association type');
        }

        return $association;
    }
}

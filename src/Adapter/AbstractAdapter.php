<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Adapter;

use Zeal\Orm\Adapter\AdapterInterface;
use Zeal\Orm\Model\Association\AssociationInterface;
use Zeal\Orm\Model\Association;

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
     * Returns all adapter options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns true if the adapter has an option with the specified key
     *
     * @param  string  $key
     * @return boolean
     */
    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
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
        if (!$this->hasOption($key)) {
            return $default;
        }

        return $this->options[$key];
    }
}

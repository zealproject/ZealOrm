<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Identity;

class Map
{
    static protected $cachedObjects = array();

    /**
     * Store an object
     *
     * @param  object $object
     * @param  mixed $key
     * @return void
     */
    public function store($object, $key)
    {
        if (!is_scalar($key)) {
            throw new \Exception('Only scalar values can be used as keys for the identity map');
        }

        $class = get_class($object);

        if (!isset(static::$cachedObjects[$class])) {
            static::$cachedObjects[$class] = array();
        }

        static::$cachedObjects[$class][$key] = $object;
    }

    /**
     * Returns the cached object
     *
     * @param  string $class
     * @param  mixed $key
     * @return object|null
     */
    public function get($class, $key)
    {
        if ($this->isCached($class, $key)) {
            return static::$cachedObjects[$class][$key];
        }

        return null;
    }

    /**
     * Returns whether or not an object with the supplied details is cached
     *
     * @param  string  $class The class name
     * @param  mixed  $key    Key value (typically primary key)
     * @return boolean
     */
    public function isCached($class, $key)
    {
        return (isset(static::$cachedObjects[$class]) && isset(static::$cachedObjects[$class][$key]));
    }
}

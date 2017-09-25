<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Identity;

class Map
{
    /**
     * @var array
     */
    static protected $cachedObjects = array();

    /**
     * Store an object
     *
     * @param  object $object
     * @param  mixed $cacheKey
     * @return void
     */
    public function store($object, $cacheKey)
    {
        if (!is_scalar($cacheKey)) {
            throw new \Exception('Only scalar values can be used as cacheKeys for the identity map');
        }

        $class = get_class($object);

        if (!isset(static::$cachedObjects[$class])) {
            static::$cachedObjects[$class] = array();
        }

        static::$cachedObjects[$class][$cacheKey] = $object;
    }

    /**
     * Returns the cached object
     *
     * @param  string $class
     * @param  mixed $cacheKey
     * @return object|null
     */
    public function get($class, $cacheKey)
    {
        if ($this->isCached($class, $cacheKey)) {
            return static::$cachedObjects[$class][$cacheKey];
        }

        return null;
    }

    /**
     * Remove a cached object
     *
     * @param  string $class
     * @param  mixed $cacheKey
     * @return void
     */
    public function remove($class, $cacheKey)
    {
        if ($this->isCached($class,$cacheKey)) {
            unset(static::$cachedObjects[$class][$cacheKey]);
        }
    }

    /**
     * Returns whether or not an object with the supplied details is cached
     *
     * @param  string  $class The class name
     * @param  mixed  $cacheKey    Key value (typically primary cacheKey)
     * @return boolean
     */
    public function isCached($class, $cacheKey)
    {
        return (isset(static::$cachedObjects[$class]) && isset(static::$cachedObjects[$class][$cacheKey]));
    }
}

<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use ZealOrm\Identity\Map as IdentityMap;

class CacheListener extends AbstractListenerAggregate
{
    protected $serviceLocator;

    protected $cache;

    protected $attachTo;

    public function __construct($sm, $cache, $attachTo = array('mapper'))
    {
        $this->serviceLocator = $sm;
        $this->cache = $cache;
        $this->attachTo = $attachTo;
    }

    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();

        $this->listeners[] = $sharedEvents->attach($this->attachTo, 'find.pre', array($this, 'checkCache'), 500);
        $this->listeners[] = $sharedEvents->attach($this->attachTo, 'find.post', array($this, 'storeInCache'), -500);
        $this->listeners[] = $sharedEvents->attach($this->attachTo, array('update.post', 'delete.post'), array($this, 'removeFromCache'), 500);
    }

    /**
     * Returns a cache key unique to the supplied class name and id
     *
     * Returns the resulting key, or false if this object cannot be
     * cached
     *
     * @param  string $className
     * @param  mixed $id
     * @param  array|null $params
     * @return string|false
     */
    protected function buildCacheKey($className, $id, $params = null)
    {
        $cacheKey = $className.'_'.$id;
        if ($params) {
            // TODO
        }

        return $cacheKey;
    }

    /**
     * If an object exists in the cache, return it
     *
     * @param  EventInterface $e
     * @return object|null
     */
    public function checkCache(EventInterface $e)
    {
        $params = $e->getParams();
        $mapper = $e->getTarget();
        $id = $params['id'];

        $success = null;

        $cacheKey = $this->buildCacheKey($mapper->getClassName(), $id);
        if ($cacheKey) {
            $data = $this->cache->getItem($this->buildCacheKey($mapper->getClassName(), $id), $success);
            if ($data) {
                $object = $this->serviceLocator->get($mapper->getClassName());
                $object = $object->getHydrator()->hydrate($data, $object);
                if ($object) {
                    return $object;
                }
            }
        }

        return null;
    }

    /**
     * If the event returns an object, store this in the cache
     *
     * @param  EventInterface $e
     * @return void
     */
    public function storeInCache(EventInterface $e)
    {
        $mapper = $e->getTarget();
        $params = $e->getParams();
        $object = $params['object'];
        $id = $params['id'];

        if ($object && is_scalar($id)) {
            $data = $object->getHydrator()->extract($object);
            if ($data) {
                $cacheKey = $this->buildCacheKey($mapper->getClassName(), $id);
                if ($cacheKey) {
                    $this->cache->setItem($cacheKey, $data);
                }
            }
        }
    }

    public function removeFromCache(EventInterface $e)
    {
        $mapper = $e->getTarget();
        $params = $e->getParams();
        $object = $params['object'];
        $id = $params['id'];

        if ($object && is_scalar($id)) {
            error_log('Clearing cache');
            $cacheKey = $this->buildCacheKey($mapper->getClassName(), $id);

            $this->cache->setItem($cacheKey, $data);
        }
    }
}

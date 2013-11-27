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

        $this->listeners[] = $sharedEvents->attach($this->attachTo, array('find.pre', 'fetchObject.pre'), array($this, 'checkCache'), 500);
        $this->listeners[] = $sharedEvents->attach($this->attachTo, array('find.post', 'fetchObject.post'), array($this, 'storeInCache'), -500);
        $this->listeners[] = $sharedEvents->attach($this->attachTo, array('update.post', 'delete.post'), array($this, 'removeFromCache'), 500);
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
        $query = $params['query'];

        $cacheKey = $query->getCacheKey();
        if (!$cacheKey) {
            return;
        }

        // load object from cache
        $data = $this->cache->getItem($cacheKey);
        if ($data) {
            $object = $this->serviceLocator->get($mapper->getClassName());
            $object = $object->getHydrator()->hydrate($data, $object);
            if ($object) {
                return $object;
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
        $query = $params['query'];

        $cacheKey = $query->getCacheKey();
        if (!$cacheKey) {
            return;
        }

        if ($object) {
            $data = $object->getHydrator()->extract($object);
            if ($data) {
                $this->cache->setItem($cacheKey, $data);
            }
        }
    }

    /**
     * Removes an object from the cache
     *
     * @param  EventInterface $e
     * @return void
     */
    public function removeFromCache(EventInterface $e)
    {
        $mapper = $e->getTarget();
        $params = $e->getParams();
        $object = $params['object'];

        // build a query object for this id
        $primaryKey = $mapper->getAdapterOption('primaryKey');
        if ($primaryKey && is_scalar($primaryKey)) {
            $query = $mapper->getAdapter()->buildQuery();
            $query->setId($object->$primaryKey);

        } else {
            return;
        }

        $cacheKey = $query->getCacheKey();
        if (!$cacheKey) {
            return;
        }

        if ($cacheKey) {
            $this->cache->removeItem($cacheKey);
        }
    }
}

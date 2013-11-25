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

class IdentityMapListener extends AbstractListenerAggregate
{
    protected $identityMap;

    public function __construct(IdentityMap $identityMap)
    {
        $this->identityMap = $identityMap;
    }

    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();

        $this->listeners[] = $sharedEvents->attach('mapper', 'find.pre', array($this, 'checkIdentityMap'), 1000);
        $this->listeners[] = $sharedEvents->attach('mapper', 'find.post', array($this, 'storeInIdentityMap'), -1000);
    }

    /**
     * If an object exists in the Identity Map, return it
     *
     * @param  EventInterface $e
     * @return object|false
     */
    public function checkIdentityMap(EventInterface $e)
    {
        $params = $e->getParams();
        $mapper = $e->getTarget();
        $id = $params['id'];

        return $this->identityMap->get($mapper->getClassName(), $id);
    }

    /**
     * If the event returns an object, store this in the Identity Map
     *
     * @param  EventInterface $e
     * @return void
     */
    public function storeInIdentityMap(EventInterface $e)
    {
        $params = $e->getParams();
        $object = $params['object'];
        $id = $params['id'];

        if ($object && is_scalar($id)) {
            $this->identityMap->store($object, $id);
        }
    }

    /**
     * Remove the object from the Identity Map if it's there
     *
     * @param  EventInterface $e
     * @return void
     */
    public function removeFromCache(EventInterface $e)
    {
        $params = $e->getParams();
        $id = $params['id'];

        if (is_scalar($id)) {
            $this->identityMap->remove($object, $id);
        }
    }

}

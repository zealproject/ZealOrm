<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Model;

use ZealOrm\Hydrator\AbstractHydrator;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

class Hydrator extends AbstractHydrator
{
    /**
     * @var EventManager
     */
    protected $events;

    /**
     * Setter for the event manager
     *
     * @param EventManagerInterface $events
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__
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

    public function hydrate(array $data, $object)
    {
        $this->getEventManager()->trigger('hydrate.pre', $object);

        $object = parent::hydrate($data, $object);

        $this->getEventManager()->trigger('hydrate.post', $object);
    }
}

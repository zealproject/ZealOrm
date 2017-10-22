<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Model;

use Zeal\Orm\Hydrator\AbstractHydrator;
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

    /**
     * Hydrate a model
     *
     * @param  array  $data
     * @param  object $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        $object = parent::hydrate($data, $object);

        return $object;
    }
}
<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Mapper;

use ZealOrm\Adapter\Zend\Db;
use ZealOrm\Model\Hydrator;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Paginator\Paginator;
use ZealOrm\Mapper\Paginator\Adapter as PaginatorAdapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractMapper implements MapperInterface, EventManagerAwareInterface, ServiceLocatorAwareInterface
{
    protected $adapter;

    protected $className;

    protected $adapterOptions = array();

    protected $fields = array();

    /**
     * @var EventManager
     */
    protected $events;

    protected $hydrator;

    protected $serviceLocator;


    public function getClassName()
    {
        if (!$this->className) {
            // attempt to work out the class name from the mapper class name
            $mapperClass = get_class($this);
            if (substr($mapperClass, -6) == 'Mapper') {
                $className = substr($mapperClass, 0, -6);
                if (class_exists($className)) {
                    $this->className = $className;

                } else {
                    throw new Exception('Unable to determine class name in apper '.$mapperClass);
                }
            }
        }

        return $this->className;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Returns the name of the adapter this mapper uses. The name is passed
     * to the service manager to get an instance of the adapter, supplied to
     * the mapper on instantiation.
     *
     * By default this returns the zeal_default_adapter string, which aliases
     * to the Zend DB adapter.
     *
     * @return string
     */
    public function getAdapterName()
    {
        return 'zeal_default_adapter';
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getAdapterOptions()
    {
        return $this->adapterOptions;
    }

    /**
     * Returns the adapter option with the supplied key, defaulting to $default
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function getAdapterOption($key, $default = null)
    {
        if (array_key_exists($key, $this->adapterOptions)) {
            return $this->adapterOptions[$key];
        }

        return $default;
    }

    /**
     * Setter for the event manager, also populates identifiers
     *
     * @param EventManagerInterface $events
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            $this->getClassName(),
            'mapper'
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
     * Setter for the hydrator
     *
     * @param [type] $hydrator [description]
     */
    public function setHydrator($hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Returns the hydrator
     *
     * @return ZealOrm\Model\Hydrator [description]
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new Hydrator();
            $this->hydrator->setFields($this->getFields());
        }

        return $this->hydrator;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function buildQuery($params = null)
    {
        return $this->getAdapter()->buildQuery($params);
    }

    /**
     * Converts a data array into a object
     *
     * @param array $data
     * @param boolean $guard
     * @return object
     */
    public function arrayToObject(array $data, $guard = true)
    {
        if ($this->getServiceLocator()->has($this->getClassName())) {
            $object = clone $this->getServiceLocator()->get($this->getClassName());
        } else {
            $className = $this->getClassName();
            $object = new $className();
        }

        $this->getHydrator()->hydrate((array)$data, $object);

        return $object;
    }

    /**
     * Converts an object into an array
     *
     * @param  object $object
     * @return array
     */
    public function objectToArray($object)
    {
        return $this->getHydrator()->extract($object);
    }

    /**
     * Converts the result of an adapter query into an object
     *
     * This function is called on any data returned by the mapper's adapter. In most
     * cases this data will be in an array-type format, and so by default this calls
     * arrayToObject, but the function exists to allow custom functionality at the mapper
     * level for any adapters that return other data structures.
     *
     * @param mixed $result
     * @return object
     */
    public function resultToObject($result, $guard = true)
    {
        return $this->arrayToObject($result, $guard);
    }

    /**
     * Find an object by its $id
     *
     * Returns the object on success, false on failure
     *
     * @param  mixed $id
     * @param  Query|null $query Optional base query object
     * @return object|false
     */
    public function find($id, $query = null)
    {
        $eventParams = array(
            'id' => $id
        );

        $results = $this->getEventManager()->trigger('find.pre', $this, $eventParams, function ($r) {
            return ($r !== null);
        });
        if ($results->stopped()) {
            return $results->last();
        }

        $data = $this->getAdapter()->find($id, $query);
        if ($data) {
            $object = $this->resultToObject($data, false);

            $eventParams['object'] = $object;

            $this->getEventManager()->trigger('find.post', $this, $eventParams);

            return $object;
        }

        return false;
    }

    public function fetchAll($query = null)
    {
        if (!$query) {
            $query = $this->buildQuery();
        }

        $data = $this->getAdapter()->fetchAll($query);
        if ($data) {
            $results = array();
            foreach ($data as $result) {
                $results[] = $this->resultToObject($result, false);
            }

            return $results;
        }

        return array();
    }

    public function fetchObject($query)
    {
        $data = $this->getAdapter()->fetchRecord($query);
        if ($data) {
            return $this->resultToObject($data, false);
        }

        return false;
    }

    /**
     * Returns a paginated resultset
     *
     * @param  Query     $query
     * @param  integer   $currentPage
     * @param  integer   $itemsPerPage
     * @return Paginator
     */
    public function paginate($query, $currentPage, $itemsPerPage = 30)
    {
        if ($query === null) {
            $query = $this->buildQuery();
        }

        $paginator = new Paginator(new PaginatorAdapter($this, $query));
        $paginator->setCurrentPageNumber($currentPage);
        $paginator->setItemCountPerPage($itemsPerPage);

        return $paginator;
    }

    /**
     * Prepare the object
     *
     * This function is called before any create/save/update operation
     * and can be overridden in the mapper class as a simple way to
     * make changes to the object before save.
     *
     * @param  object $object
     * @return void
     */
    public function prepare($object)
    {

    }

    /**
     * Create an object
     *
     * @param  object   $object
     * @return boolean  returns true on success, false on failure
     */
    public function create($object)
    {
        $this->prepare($object);

        // fire the pre create event
        $this->getEventManager()->trigger('create.pre', $this, array(
            'object' => $object
        ));

        $data = $this->objectToArray($object);

        $success = $this->getAdapter()->create($data);
        if ($success) {
            // fire the post create event
            $this->getEventManager()->trigger('create.post', $this, array(
                'object' => $object
            ));
        }

        return $success;
    }

    /**
     * Create an object
     *
     * @param  object   $object
     * @return boolean  returns true on success, false on failure
     */
    public function update($object, $fields = null)
    {
        $this->prepare($object);

        // fire the pre update event
        $this->getEventManager()->trigger('update.pre', $this, array(
            'object' => $object
        ));

        $data = $this->objectToArray($object);

        $success = $this->getAdapter()->update($data, $fields);

        if ($success) {
            // fire the post update event
            $this->getEventManager()->trigger('update.post', $this, array(
                'object' => $object
            ));
        }

        return $success;
    }

    public function delete($object)
    {
        // fire the pre delete event
        $this->getEventManager()->trigger('delete.pre', $this, array(
            'object' => $object
        ));

        $data = $this->objectToArray($object);

        $success = $this->getAdapter()->delete($data);

        if ($success) {
            // fire the post delete event
            $this->getEventManager()->trigger('delete.post', $this, array(
                'object' => $object
            ));
        }

        return $success;
    }

    public function buildAssociation($type, $options = array())
    {
        return $this->getAdapter()->buildAssociation($type, $options);
    }

    public function buildQueryForAssociation($association)
    {
        $query = $this->buildQuery();

        $query = $this->getAdapter()->populateAssociationQuery($query, $association);

        return $query;
    }
}

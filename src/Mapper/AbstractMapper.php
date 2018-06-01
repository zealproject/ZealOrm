<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Mapper;

use Zeal\Orm\Adapter\Zend\Db;
use Zeal\Orm\Hydrator\ModelHydrator;
use Zend\Hydrator\HydratorInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Paginator\Paginator;
use Zeal\Orm\Mapper\Paginator\Adapter as PaginatorAdapter;

abstract class AbstractMapper implements MapperInterface, EventManagerAwareInterface
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
            } else if (strpos($mapperClass, 'Mapper\\') !== false) {
                $className = str_replace('Mapper', 'Entity', $mapperClass);
            } else {
                throw new \Exception('Unable to determine class name in mapper '.$mapperClass);
            }

            if (class_exists($className)) {
                $this->className = $className;

            } else {
                throw new \Exception('Unable to determine class name in mapper '.$mapperClass);
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
            get_class($this),
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

    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    public function getContainer()
    {
        return $this->container;
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
        $className = $this->getClassName();
        if ($this->getContainer()->has($className)) {
            $object = clone $this->getContainer()->get($className);
        } else {
            $object = new $className();
        }

        $object = $object->getHydrator()->hydrate($data, $object);

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
        return $object->getHydrator()->extract($object);
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
     * @param  array|null $params Optional array of additional params to pass to the
     *                            query object
     * @return object|false
     */
    public function find($id, $query = null, $params = null)
    {
        if (!$query) {
            $query = $this->buildQuery();
        }

        $query->setId($id, $params);

        $eventParams = array(
            'query' => $query
        );

        $results = $this->getEventManager()->trigger('find.pre', $this, $eventParams, function ($r) {
            return ($r !== null);
        });
        if ($results->stopped()) {
            return $results->last();
        }

        $data = $this->getAdapter()->fetchRecord($query);
        if ($data) {
            $object = $this->resultToObject($data, false);

            $eventParams['object'] = $object;

            $this->getEventManager()->trigger('find.post', $this, $eventParams);

            return $object;
        }

        return false;
    }

    public function getCollection($query = null)
    {
        if (!$query) {
            $query = $this->buildQuery();
        }

        return new Collection($this, $query);
    }

    /**
     * Fetch multiple objects based on the supplied query
     *
     * @param  QueryInterface $query
     * @return array
     */
    public function fetchObjects($query = null)
    {
        if (!$query) {
            $query = $this->buildQuery();
        }

        $eventParams = [
            'query' => $query
        ];

        $results = $this->getEventManager()->trigger('fetchAll.pre', $this, $eventParams, function ($r) {
            return ($r !== null);
        });
        if ($results->stopped()) {
            return $results->last();
        }

        $data = $this->getAdapter()->fetchAll($query);
        if ($data) {
            $results = [];
            foreach ($data as $result) {
                $results[] = $this->resultToObject($result, false);
            }

            $eventParams['results'] = $results;

            $this->getEventManager()->trigger('fetchAll.post', $this, $eventParams);

            return $results;
        }

        return [];
    }

    /**
     * Fetch a single object based on the supplied query
     *
     * @param  QueryInterface $query
     * @return object|false
     */
    public function fetchObject($query)
    {
        $eventParams = array(
            'query' => $query
        );

        $results = $this->getEventManager()->trigger('fetchObject.pre', $this, $eventParams, function ($r) {
            return ($r !== null);
        });
        if ($results->stopped()) {
            return $results->last();
        }

        $data = $this->getAdapter()->fetchRecord($query);
        if ($data) {
            $object = $this->resultToObject($data, false);

            $eventParams['object'] = $object;

            $this->getEventManager()->trigger('fetchObject.post', $this, $eventParams);

            return $object;
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

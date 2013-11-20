<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm;

use Zend\Mvc\MvcEvent;
use ZealOrm\Adapter\Zend\Db;
use ZealOrm\Orm;
use Zend\EventManager\SharedEventManager;
use ZealOrm\Identity\Map as IdentityMap;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        Orm::setServiceLocator($e->getApplication()->getServiceManager());

        $identityMap = $e->getApplication()->getServiceManager()->get('ZealOrm\Identity\Map');

        $events = $e->getApplication()->getEventManager()->getSharedManager();

        // populate the source model into association objects after hydration
        $events->attach('ZealOrm\Model\Hydrator', 'hydrate.post', function ($e) {
            $model = $e->getTarget();

            foreach ($model->getAssociations() as $association) {
                $association->setSource($model);
            }

        }, 100);

        // if an auto incrementing primary key is being used, ensure it is
        // populated after creation when using the DB adapter
        $events->attach('mapper', 'create.post', function ($e) {
            $mapper = $e->getTarget();
            $params = $e->getParams();
            $object = $params['object'];

            $mapper = $params['mapper'];
            $adapter = $mapper->getAdapter();
            if ($adapter instanceof Db) {
                $primaryKey = $mapper->getAdapterOption('primaryKey');
                if ($primaryKey && $mapper->getAdapterOption('autoIncrement', true)) {
                    $id = $mapper->getAdapter()->getTableGateway()->getAdapter()->getDriver()->getLastGeneratedValue();
                    if (is_scalar($id)) {
                        $object->$primaryKey = $id;
                    }
                }
            }

        }, 999);

        // save associated data
        $events->attach('mapper', array('create.post', 'update.post'), function ($e) {
            $mapper = $e->getTarget();
            $params = $e->getParams();
            $object = $params['object'];

            $associationsToSave = $object->getAssociationsWithUnsavedData();
            if ($associationsToSave) {
                foreach ($associationsToSave as $shortname => $association) {
                    $associationMapper = $association->getTargetMapper();
                    $adapter = $associationMapper->getAdapter();

                    $association->saveData($object, $adapter);
                }
            }

        }, 900);

        // check the identity map for cached objects
        $events->attach('mapper', 'find.pre', function ($e) use ($identityMap) {
            $params = $e->getParams();
            $mapper = $e->getTarget();
            $id = $params['id'];

            return $identityMap->get($mapper->getClassName(), $id);

        }, 100);

        // store objects loaded via. find in the identity map
        $events->attach('mapper', 'find.post', function ($e) use ($identityMap) {
            $params = $e->getParams();
            $object = $params['object'];
            $id = $params['id'];

            if ($object && is_scalar($id)) {
                $identityMap->store($object, $id);
            }

        }, -100);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'ZealOrm\Adapter\Zend\Db' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

                    $adapter = new Db($dbAdapter);

                    return $adapter;
                },

                'ZealOrm\Identity\Map' => function ($sm) {
                    $map = new IdentityMap();

                    return $map;
                }
            ),

            'abstract_factories' => array(
                'ZealOrm\Service\AbstractMapperFactory',
                'ZealOrm\Service\AbstractModelFactory',
            ),

            'aliases' => array(
                'zeal_default_adapter' => 'ZealOrm\Adapter\Zend\Db'
            ),

            'shared' => array(
                // the DB adapter includes mapper-specific options, so we always
                // want a new instance of this
                'ZealOrm\Adapter\Zend\Db' => false,
            ),
        );
    }
}

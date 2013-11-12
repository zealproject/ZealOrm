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

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        Orm::setServiceLocator($e->getApplication()->getServiceManager());

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
            $object = $e->getTarget();
            $params = $e->getParams();

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
        $events->attach('mapper', 'create.post', function ($e) {
            $object = $e->getTarget();
            $params = $e->getParams();

            $associationsToSave = $object->getAssociationsWithUnsavedData();
            if ($associationsToSave) {
                foreach ($associationsToSave as $shortname => $association) {
                    $mapper = $association->getTargetMapper();
                    $adapter = $mapper->getAdapter();

                    $adapter->saveAssociatedData($object, $association);
                }
            }

        }, 900);
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
                'ZealOrm\Adapter\Zend\Db' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

                    $adapter = new Db($dbAdapter);

                    return $adapter;
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

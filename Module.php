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

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        Orm::setServiceLocator($e->getApplication()->getServiceManager());
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
                'ZealOrm\Services\MapperAbstractFactory'
            ),
        );
    }
}

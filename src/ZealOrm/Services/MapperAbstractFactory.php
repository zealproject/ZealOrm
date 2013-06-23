<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Services;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MapperAbstractFactory implements AbstractFactoryInterface
{
    /**
     * [canCreateServiceWithName description]
     *
     * @param  ServiceLocatorInterface $serviceLocator [description]
     * @param  [type]                  $name           [description]
     * @param  [type]                  $requestedName  [description]
     * @return [type]                                  [description]
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (substr($requestedName, -6) == 'Mapper' && class_exists($requestedName)) {
            return true;
        }

        return false;
    }

    /**
     * [createServiceWithName description]
     *
     * @param  ServiceLocatorInterface $serviceLocator [description]
     * @param  [type]                  $name           [description]
     * @param  [type]                  $requestedName  [description]
     * @return [type]                                  [description]
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $mapper = new $requestedName();

        $adapter = clone $serviceLocator->get('ZealOrm\Adapter\Zend\Db');
        $adapter->setOptions($mapper->getAdapterOptions());

        $mapper->setAdapter($adapter);

        return $mapper;
    }
}

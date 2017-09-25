<?php

namespace Zeal\Orm\Mvc\Service;

use Zeal\Orm\OrmManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OrmManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $ormManager = new OrmManager();
        $ormManager->setServiceLocator($serviceLocator);
        $ormManager->addPeeringServiceManager($serviceLocator);

        //$config = $serviceLocator->get('Config');

        // if (isset($config['di']) && isset($config['di']['allowed_controllers']) && $serviceLocator->has('Di')) {
        //     $controllerLoader->addAbstractFactory($serviceLocator->get('DiStrictAbstractServiceFactory'));
        // }

        return $ormManager;
    }
}

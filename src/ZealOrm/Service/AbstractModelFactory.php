<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Service;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZealOrm\Model\Hydrator as ModelHydrator;

class AbstractModelFactory implements AbstractFactoryInterface
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
        $config = $serviceLocator->get('Config');
        if (isset($config['models']) && isset($config['models'][$requestedName])) {
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
        $config = $serviceLocator->get('Config');
        $modelConfig = $config['models'][$requestedName];

        if ($serviceLocator->has($requestedName, false)) {
            $model = $serviceLocator->get($requestedName);

        } else {
            $model = new $requestedName();
        }

        foreach ($modelConfig['associations'] as $associationShortname => $options) {
            //$association = $serviceLocator->get($associationConfig['type']);
            $associationClassName = $options['type'];
            unset($options['type']);

            $association = new $associationClassName($options);

            if ($association) {
                $association->setShortname($associationShortname)
                            //->setSource($model)
                            ->setTargetClassName($options['class']);


                $model->addAssociation($associationShortname, $association);

                $association->getEventManager()->trigger('init', $association, array(
                    'model' => $model
                ));
            }
        }

        $mapper = $serviceLocator->get($requestedName.'Mapper');

        // set hydrator
        $hydrator = new ModelHydrator();
        $hydrator->setFields($mapper->getFields());

        $model->setHydrator($hydrator);


        return $model;
    }
}
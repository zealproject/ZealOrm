<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm;

use ZealOrm\Mapper\Registry;

class Orm
{
    /**
     * @var Zend\ServiceManager\ServiceLocatorInterface
     */
    protected static $serviceLocator;

    /**
     * @var array
     */
    protected static $config;

    /**
     * Setter for the service locator
     *
     * @param Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public static function setServiceLocator($serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;

        // extract some of the config as well
        $config = $serviceLocator->get('Config');
        if (isset($config['zeal_orm'])) {
            self::$config = $config['zeal_orm'];
        }
    }

    /**
     * Getter for the service locator
     *
     * @return Zend\ServiceManager\ServiceLocatorInterface
     */
    public static function getServiceLocator()
    {
        return self::$serviceLocator;
    }

    /**
     * Returns the mapper for the supplied model class
     *
     * @param  string|object $objectOrClassName Class name or model instance
     * @return ZealOrm\Mapper\MapperInterface
     */
    public static function getMapper($objectOrClassName)
    {
        if (is_object($objectOrClassName)) {
            $objectOrClassName = get_class($objectOrClassName);
        } else if (empty($objectOrClassName)) {
            throw new \Exception('Unable to load mapper class for empty class name');
        }

        if (isset(self::$config['model_aliases']) && isset(self::$config['model_aliases'][$objectOrClassName])) {
            // return the mapper for the target class
            return self::getMapper(self::$config['model_aliases'][$objectOrClassName]);
        }

        $mapperClassName = self::getMapperClassName($objectOrClassName);

        return self::getServiceLocator()->get($mapperClassName);
    }

    /**
     * Returns the default adapter
     *
     * This uses the service locator's zeal_default_adapter alias, which
     * by default points at the Zend DB adapter
     *
     * @return ZealOrm\Adapter\AdapterInterface
     */
    public static function getDefaultAdapter()
    {
        return self::getServiceLocator()->get('zeal_default_adapter');
    }

    /**
     * Returns mapper class name for a given class
     *
     * @param  string $className
     * @return string
     */
    public static function getMapperClassName($className)
    {
        // TODO do this with a closure instead so the mapper class name convention can
        // easily be changed?
        return $className.'Mapper';
    }
}

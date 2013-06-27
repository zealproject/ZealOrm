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
    protected static $serviceLocator;

    protected static $mapperRegistry;

    public static function setServiceLocator($serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;
    }

    public static function getServiceLocator()
    {
        return self::$serviceLocator;
    }

    public static function getMapper($objectOrClassName)
    {
        if (is_object($objectOrClassName)) {
            $objectOrClassName = get_class($objectOrClassName);
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

    public static function getMapperClassName($className)
    {
        // TODO do this with a closure instead so the mapper class name convention can
        // easily be changed?
        return $className.'Mapper';
    }
}

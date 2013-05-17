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

    public static function getMapperClassName($className)
    {
        // TODO do this with a closure instead so the mapper class name convention can
        // easily be changed?
        return $className.'Mapper';
    }
}
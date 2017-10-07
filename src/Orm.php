<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm;

use Zeal\Orm\Mapper\Registry;

class Orm
{
    /**
     * @var array
     */
    protected static $config;

    protected static $defaultAdapter;

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
        // return self::getServiceLocator()->get('zeal_default_adapter');
        return static::$defaultAdapter;
    }

    public static function setDefaultAdapter($adapter)
    {
        static::$defaultAdapter = $adapter;
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

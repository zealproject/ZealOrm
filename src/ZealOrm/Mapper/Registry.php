<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Mapper;

class Registry
{
    protected $instances = array();

    public function getMapper($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        if (!isset($this->instances[$className])) {
            $mapperClassName = $this->getMapperClassName($className);
            $this->instances[$className] = new $mapperClassName();
        }

        return $this->instances[$className];
    }

    public function getMapperClassName($className)
    {
        return "Foo\\{$className}";
    }
}
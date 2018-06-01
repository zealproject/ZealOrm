<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm;

use Zeal\Orm\Mapper\Registry;

class Orm
{
    protected static $adapters = [];

    public static function getAdapter($key)
    {
        return static::$adapters[$key];
    }

    public static function setAdapter($key, $adapter)
    {
        static::$adapters[$key] = $adapter;
    }
}

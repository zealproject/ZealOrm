<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm;

class DateTime extends \DateTime
{
    static $defaultFormat = 'H:i, d/m/Y';

    public function __construct($time = null)
    {
        if ($time) {
            if ($time instanceof Zend_Db_Expr) {
                // might be NOW()
                if ($time->__toString() == 'NOW()') {
                    $time = date('Y-m-d H:i:s');
                } else {
                    throw new \Exception('Invalid date parameter supplied to DateTime');
                }
            } else if (is_numeric($time) && $time > 0) {
                // assume unix timestamp
                $time = '@'.$time;
            }
        } else {
            $time = '@'.time();
        }

        parent::__construct($time);
    }

    /**
     * Converts the datetime into a string using the default format
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format(self::$defaultFormat);
    }
}

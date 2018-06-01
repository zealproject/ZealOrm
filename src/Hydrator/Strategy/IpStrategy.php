<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;

class IpStrategy implements StrategyInterface
{
    /**
     * Converts the given value so that it can be extracted by the hydrator.
     *
     * @param  integer $value The original value.
     * @return int Returns the value that should be extracted.
     */
    public function extract($value)
    {
        return ip2long($value);
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param  int|string $value The original value.
     * @return int Returns the value that should be hydrated.
     */
    public function hydrate($value)
    {
        if (is_numeric($value)) {
            return long2ip($value);
        }

        throw new \Exception(sprintf("Unexpected value %s can't be hydrated.", $value));
    }
}

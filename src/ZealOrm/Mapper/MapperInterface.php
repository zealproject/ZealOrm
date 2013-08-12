<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Mapper;

interface MapperInterface
{
    public function getAdapterName();

    public function arrayToObject(array $data, $guard = true);

    public function objectToArray($object);
}

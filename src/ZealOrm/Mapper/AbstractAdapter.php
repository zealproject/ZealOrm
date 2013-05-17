<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Mapper;

abstract class AbstractAdapter
{
    protected $options = array();

    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOption($key, $default = null)
    {
        return $this->options[$key];
    }
}
<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Adapter;

interface AdapterInterface
{
    public function buildAssociation($type, $options);

    public function setOptions(array $options);

    public function getOption($key, $default = null);

    public function buildQuery();
}

<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm;

use IteratorAggregate;
use ArrayIterator;
use Countable;

interface CollectionInterface extends IteratorAggregate, Countable
{
    public function getQuery();

    public function getData();

    /**
     * Returns the number of items in the collection
     *
     * @return integer
     */
    public function count();

    public function first();
}

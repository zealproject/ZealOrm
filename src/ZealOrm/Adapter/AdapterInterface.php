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

    /**
     * Returns a query object for this adapter
     *
     * @param  array|null $params
     * @return Query\QueryInterface
     */
    public function buildQuery($params = null);

    /**
     * Creates an object
     *
     * @param mixed $object
     * @return boolean
     */
    public function create($data);

    /**
     * Commits any changes to the object
     *
     * @param mixed $object
     * @return boolean
     */
    public function update($data);

    /**
     * Creates an object if it is new, updates it otherwise
     *
     * @param mixed $object
     * @return boolean
     */
    public function save($data);

    /**
     * Deletes an object
     *
     * @param mixed $object
     * @return boolean
     */
    public function delete($data);
}

<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Adapter;

use ZealOrm\Adapter\Query\QueryInterface;
use ZealOrm\Model\Association\AssociationInterface;

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
     * Fetch multiple records
     *
     * @param  QueryInterface $query
     * @return array|false
     */
    public function fetchAll(QueryInterface $query = null);

    /**
     * Fetch a single record
     *
     * @param  QueryInterface $query
     * @return array|false
     */
    public function fetchRecord(QueryInterface $query = null);

    /**
     * Finds a single record based on its primary key
     *
     * @param  mixed $id
     * @param  QueryInterface $query
     * @return array|false
     */
    public function find($id, $query = null);

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

    /**
     * Saves data from association $association, from the source object
     *
     * @param  object               $object
     * @param  AssociationInterface $association
     * @return boolean
     */
    public function saveAssociatedData($object, AssociationInterface $association);
}

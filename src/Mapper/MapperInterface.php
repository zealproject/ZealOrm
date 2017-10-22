<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Mapper;

interface MapperInterface
{
    public function getAdapterName();

    public function arrayToObject(array $data, $guard = true);

    public function objectToArray($object);

    public function buildQuery($params = null);

    public function find($id, $query = null);

    public function fetchAll($query = null);

    public function fetchObject($query);

    public function paginate($query, $currentPage);

    public function prepare($object);

    public function create($object);

    public function update($object, $fields = null);

    public function delete($object);
}
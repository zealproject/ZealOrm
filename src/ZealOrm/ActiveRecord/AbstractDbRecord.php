<?php

namespace ZealOrm\ActiveRecord;

use ZealOrm\ActiveRecord\AbstractActiveRecord;

class AbstractDbRecord extends AbstractActiveRecord
{
    /**
     * The database table name
     *
     * @var string
     */
    protected static $tableName;

    /**
     * Name of the primary key column, or an array of column names
     * (for compound keys)
     *
     * @var string|array
     */
    protected $primaryKey;

    protected static $db;

    protected static $tableGateway;


    public function getTableGateway()
    {
        if (!static::$tableGateway) {
            static::$tableGateway = new TableGateway($this->getTableName(), static::$db);
        }

        return static::$tableGateway;
    }

    public static function reflectTableName($className)
    {
        $classParts = explode("\\", $className);
        $className = array_pop($classParts);
        $className = strtolower($className).'s';

        return $className;
    }

    public static function getDefaultAdapterOptions()
    {
        $tableName = static::$tableName ? static::$tableName : static::reflectTableName(get_called_class());

        return array(
            'tableName' => $tableName
        );
    }

    public function isNewRecord()
    {

    }

    protected function buildWhereClause()
    {

    }

    public static function find($id)
    {

    }

    public static function first()
    {
        $adapter = static::getStaticAdapter();

        $query = $adapter->buildQuery();
        $query->limit(1);

        $data = $adapter->fetchObject($query);
        if ($data) {
            $object = new static();
            $object->getHydrator()->hydrate($data, $object);

            return $object;
        }

        return false;
    }

    public static function all()
    {
        $adapter = static::getStaticAdapter();

        $query = $adapter->buildQuery();

        $data = $adapter->fetchAll($query);
        if ($data) {
            $results = array();

            foreach ($data as $row) {
                $object = new static();
                $object->getHydrator()->hydrate($row, $object);

                $results[] = $object;
            }

            return $results;
        }

        return false;
    }

    public static function where($params)
    {
        $adapter = static::getStaticAdapter();

        $query = $adapter->buildQuery();
        $query->where($params);

        $data = $adapter->fetchObject($query);
        if ($data) {
            $object = new static();
            $object->getHydrator()->hydrate($data, $object);

            return $object;
        }

        return false;
    }

    public static function create(array $data)
    {
        $object = new static();
        $object->getHydrator()->hydrate($data, $object);

        $data = $object->getHydrator()->extract($object);

        if ($this->getTableGateway()->insert($data)) {
            return $object;
        }

        return false;
    }

    public function update()
    {
        $data = $this->getHydrator()->extract($object);

        return $this->getTableGateway()->update($data, $this->buildWhereClause());
    }

    public function save()
    {
        if ($this->isNewRecord()) {
            return $this->create();
        } else {
            return $this->update();
        }
    }

    public function delete()
    {
        return $this->getTableGateway()->delete($this->buildWhereClause());
    }
}

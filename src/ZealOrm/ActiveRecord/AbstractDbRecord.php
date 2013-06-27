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
    protected $tableName;


    public function getTableName()
    {
        if (!$this->tableName) {
            $this->tableName = $this->reflectTableName(get_class($this));
        }

        return $this->tableName;
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
        return array(
            'tableName' => static::reflectTableName(get_called_class())
        );
    }

    public static function find($id)
    {

    }

    public static function first()
    {

    }

    public static function all()
    {

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

        if ($object->getAdapter()->create($data)) {
            return $object;
        }

        return false;
    }

    public function update()
    {
        $data = $this->getHydrator()->extract($this);

    }

    public function save()
    {

    }

    public function delete()
    {

    }
}

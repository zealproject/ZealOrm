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

        $success = $object->getAdapter()->create($data);
        if ($object->getAdapter()->getOption('autoIncrement', true)) {
            // $success is actually the newly created ID, so put it in the object
            // FIXME: this is a bit DB specific
            $object->$id = $id;
        }

        if ($success) {
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

<?php

namespace ZealOrm\ActiveRecord;

use ZealOrm\ActiveRecord\AbstractActiveRecord;
use ZealOrm\Collection;

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
    protected static $primaryKey;

    /**
     * [$defaultAdapterOptions description]
     * @var array|null
     */
    protected static $defaultAdapterOptions = array(
        'autoIncrement' => true
    );

    protected static $db;

    protected static $tableGateway;


    public function getTableGateway()
    {
        if (!static::$tableGateway) {
            static::$tableGateway = new TableGateway($this->getTableName(), static::$db);
        }

        return static::$tableGateway;
    }

    /**
     * Setter for database table name
     *
     * @param string $tableName
     */
    public static function setTableName($tableName)
    {
        static::$tableName = $tableName;
    }

    /**
     *  Getter for database table name
     *
     *  If not populated, the reflection is used to guess the table name
     *  based on class name
     *
     * @return string
     */
    public static function getTableName()
    {
        if (!static::$tableName) {
            static::$tableName = static::reflectTableName(get_called_class());
        }

        return static::$tableName;
    }

    public static function reflectTableName($className)
    {
        $classParts = explode("\\", $className);
        $className = array_pop($classParts);
        $className = strtolower($className).'s';

        return $className;
    }

    /**
     * Setter for primary key
     *
     * @param string|array $primaryKey
     */
    public static function setPrimaryKey($primaryKey)
    {
        static::$primaryKey = $primaryKey;
    }

    /**
     * Getter for primary key
     *
     * @return string|array
     */
    public static function getPrimaryKey()
    {
        if (static::$primaryKey === null) {
            return 'id'; // TODO, some reflection here?
        }

        return static::$primaryKey;
    }

    public static function getDefaultAdapterOptions()
    {
        $classDefaults = static::$defaultAdapterOptions;
        if (!is_array($classDefaults)) {
            $classDefaults = array();
        }

        $tableName = static::getTableName();
        $primaryKey = static::getPrimaryKey();

        return array_merge(array(
            'tableName' => $tableName,
            'primaryKey' => $primaryKey
        ), $classDefaults);
    }

    public function isNewRecord()
    {

    }

    public static function find($id)
    {
        return static::where(array('id' => $id))->getFirstRow();
    }

    public static function first()
    {
        $adapter = static::getStaticAdapter();

        $query = $adapter->buildQuery();
        $query->limit(1);

        $data = $adapter->fetchRecord($query);
        if ($data) {
            $object = new static();
            $object->getHydrator()->hydrate($data, $object);

            return $object;
        }

        return false;
    }

    public static function all()
    {
        return static::buildCollection();
    }

    public static function where($params)
    {
        $collection = static::buildCollection();
        $collection->getQuery()->where($params);

        return $collection;
    }

    public static function order($orderSql)
    {
        $collection = static::buildCollection();
        $collection->getQuery()->order($orderSql);

        return $collection;
    }

    public function create()
    {
        $data = $this->getHydrator()->extract($this);

        $success = $this->getAdapter()->create($data);
        if ($this->getAdapter()->getOption('autoIncrement', true)) {
            $id = 'id';
            // $success is actually the newly created ID, so put it in the object
            $this->$id = $id;
        }

        return $success ? true : false;
    }

    public function update()
    {
        $data = $this->getHydrator()->extract($this);

        return $this->getAdapter()->update($data);
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
        $data = $this->getHydrator()->extract($this);

        return $this->getAdapter()->delete($data);
    }

    /**
     * Calculate the entity's fields by inspecting the table structure
     *
     * TODO: cache this!
     *
     * @return array
     */
    protected static function getFieldsFromTableStructure()
    {
        $db = static::getStaticAdapter()->getDb();
        $statement = $db->query("DESCRIBE ".static::getTableName());
        $result = $statement->execute();

        $fields = array();
        foreach ($result as $column) {
            $type = substr($column['Type'], 0, strpos($column['Type'], '('));
            if ($type == 'int') {
                $type = 'integer';
            } else {
                // FIXME: need mapping for more types here
                $type = 'string';
            }

            $fields[$column['Field']] = $type;
        }

        return $fields;
    }

    /**
     * Returns the model's fields
     *
     * @return array
     */
    public function getFields()
    {
        if (!static::$fields) {
            $fields = static::getFieldsFromTableStructure();

            static::$fields = $fields;
        }

        return static::$fields;
    }
}

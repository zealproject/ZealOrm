<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\ActiveRecord;

use Zeal\Orm\Adapter\Zend\Db as DbAdapter;
use Zeal\Orm\Orm;

class AbstractDbRecord extends AbstractActiveRecord
{
    /**
     * @var array|null
     */
    protected static $defaultAdapterOptions = array(
        'autoIncrement' => true
    );

    public static function getStaticAdapter()
    {
        $adapter = Orm::getAdapter(DbAdapter::class);

        $adapter->setOptions(static::getDefaultAdapterOptions());

        return $adapter;
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
            static::$tableName = static::reflectTableName(static::class);
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
            $classDefaults = [];
        }

        $tableName = static::getTableName();
        $primaryKey = static::getPrimaryKey();

        return array_merge([
            'tableName' => $tableName,
            'primaryKey' => $primaryKey
        ], $classDefaults);
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

        $fields = [];
        foreach ($result as $column) {
            if (strpos($column['Type'], '(') !== false) {
                $type = substr($column['Type'], 0, strpos($column['Type'], '('));
            } else {
                $type = $column['Type'];
            }
            if (in_array($type, ['int', 'mediumint'])) {
                $type = 'integer';
            } else if (in_array($type, ['timestamp', 'datetime'])) {
                $type = 'datetime';
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

    public static function find($id)
    {
        return static::where([static::getPrimaryKey() => $id])->getFirstRow();
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

    public static function order($order)
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
}

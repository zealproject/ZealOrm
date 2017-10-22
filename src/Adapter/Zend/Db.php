<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Adapter\Zend;

use Zend\Db\TableGateway\TableGateway;
use Zeal\Orm\Adapter\Zend\Db\Sql\Sql;
use Zeal\Orm\Adapter\AbstractAdapter;
use Zeal\Orm\Model\Association\AssociationInterface;
use Zeal\Orm\Adapter\Query\QueryInterface;

class Db extends AbstractAdapter
{
    protected $db;

    protected $tableGateway;

    protected $tableName;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getDb()
    {
        return $this->db;
    }

    public function getTableName()
    {
        if (empty($this->options['tableName'])) {
            throw new \Exception('Unable to use Zend DB adapter without a tableName');
        }

        return $this->options['tableName'];
    }

    public function getPrimaryKey()
    {
        return $this->options['primaryKey'];
    }

    public function getTableGateway()
    {
        if (!$this->tableGateway) {
            $this->tableGateway = new TableGateway($this->getTableName(), $this->db);
        }

        return $this->tableGateway;
    }

    public function buildQuery($params = null)
    {
        $sql = new Sql($this->db);

        if (!is_array($params)) {
            $params = array();
        }

        // default to a select object
        if (!isset($params['type'])) {
            $params['type'] = 'select';
        }

        switch ($params['type']) {
            case 'select':
                $query = $sql->select();
                break;

            case 'delete':
                $query = $sql->delete();
                break;
        }

        $query->from($this->getTableName());
        $query->setPlatform($this->getDb()->getPlatform());
        $query->setPrimaryKey($this->getPrimaryKey());

        return $query;
    }

    public function find($id, $query = null)
    {
        if (!$query) {
            $query = $this->buildQuery();
        }
        $query->where(array($this->getPrimaryKey() => $id));
        //$this->getTableName().'.'.$this->getPrimaryKey().' = ?', $id);

        return $this->fetchRecord($query);
    }

    public function fetchAll(QueryInterface $query = null)
    {
        $sql = new Sql($this->db);

        //echo $sql->getSqlStringForSqlObject($query);

        $statement = $sql->prepareStatementForSqlObject($query);
        $result = $statement->execute();

        $results = array();
        foreach ($result as $row) {
            $results[] = $row;
        }

        return $results;
    }

    public function fetchRecord(QueryInterface $query = null)
    {
        $sql = new Sql($this->db);

        $query->limit(1);

        $statement = $sql->prepareStatementForSqlObject($query);
        $result = $statement->execute();

        // TODO tidy this up
        $results = array();
        foreach ($result as $row) {
            return $row;
        }
    }

    protected function buildWhereClause($data)
    {
        $primaryKey = $this->options['primaryKey'];

        if (empty($primaryKey)) {
            throw new \Exception('Unable to build a where clause without a primary key');
        }

        if (empty($data[$primaryKey])) {
            throw new \Exception('Unable to build where clause without a value for '.htmlspecialchars($primaryKey));
        }

        $where = $this->db->platform->quoteIdentifier($primaryKey) . ' = ' . $this->db->platform->quoteValue($data[$primaryKey]);

        return $where;
    }

    public function create($data)
    {
        $this->getTableGateway()->insert($data);

        if ($this->getOption('autoIncrement', true)) {
            return $this->getTableGateway()->getAdapter()->getDriver()->getLastGeneratedValue();
        }

        return true;
    }

    public function save($data)
    {

    }

    public function update($data, array $fields = null)
    {
        $result = $this->getTableGateway()->update($data, $this->buildWhereClause($data));

        // Table gateway's update() returns the number of affected rows, which could be 0
        // even if the update worked
        return is_int($result) && $result >= 0;
    }

    public function delete($data)
    {
        return $this->getTableGateway()->delete($this->buildWhereClause($data));
    }
}
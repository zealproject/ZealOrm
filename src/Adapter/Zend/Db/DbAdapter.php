<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Adapter\Zend\Db;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;
use Zend\Db\Sql\Expression;
use Zeal\Orm\Adapter\Zend\Db\Sql\Sql;
use Zeal\Orm\Adapter\AbstractAdapter;
use Zeal\Orm\Model\Association\AssociationInterface;
use Zeal\Orm\Adapter\Query\QueryInterface;

class DbAdapter extends AbstractAdapter
{
    /**
     * @var ZendDbAdapter
     */
    protected $db;

    protected $tableGateway;

    /**
     * @var string
     */
    protected $tableName;

    public function __construct(ZendDbAdapter $db)
    {
        $this->db = $db;
    }

    /**
     * Returns the Zend DB adapter
     *
     * @return ZendDbAdapter
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Returns the database table name
     *
     * @return string the database table name
     */
    public function getTableName()
    {
        if (empty($this->options['tableName'])) {
            throw new \Exception('Unable to use Zend DB adapter without a tableName');
        }

        return $this->options['tableName'];
    }

    /**
     * Returns the primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->options['primaryKey'];
    }

    /**
     * Returns the TableGateway object
     *
     * @return TableGateway
     */
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
        $query->where([$this->getPrimaryKey() => $id]);

        return $this->fetchRecord($query);
    }

    public function fetchAll(QueryInterface $query = null)
    {
        $sql = new Sql($this->db);

        //echo $sql->getSqlStringForSqlObject($query);

        $statement = $sql->prepareStatementForSqlObject($query);
        $result = $statement->execute();

        $results = [];
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

        return $result->current();
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

    public function count($query)
    {
        $countQuery = clone $query;

        $countQuery->columns(['count' => new Expression('COUNT(*)')]);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($countQuery);
        $result = $statement->execute();

        $data = $result->current();
        return intval($data['count']);
    }
}

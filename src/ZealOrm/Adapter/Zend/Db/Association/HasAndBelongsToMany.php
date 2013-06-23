<?php

namespace ZealOrm\Adapter\Zend\Db\Association;

use ZealOrm\Adapter\Zend\Db\Association\AbstractAssociation;

class HasAndBelongsToMany extends AbstractAssociation
{
    public function getTableName()
    {
        return $this->getTargetMapper()->getAdapter()->getOption('tableName');
    }

    public function getLookupTable()
    {
        if ($this->hasOption('lookupTable')) {
            $lookupTable = $this->getOption('lookupTable');
        } else {
            $tables = array(
                $this->getTargetMapper()->getAdapter()->getTableName(),
                $this->getSourceMapper()->getAdapter()->getTableName()
            );
            sort($tables);
            //$lookupTable = $tables[0].ucfirst($tables[1]);
            $lookupTable = $tables[0].'_'.$tables[1];
        }

        return $lookupTable;
    }

    public function buildQuery()
    {
        $lookupTable = $this->getLookupTable();

        $tableName = $this->getTableName();
        $foreignKey = $this->getOption('foreignKey', $this->getSourceMapper()->getAdapter()->getPrimaryKey());
        $foreignKeyValueColumn = $this->getOption('foreignKeyValueColumn', $foreignKey);
        $associationForeignKey = $this->getOption('associationForeignKey', $this->getTargetMapper()->getAdapter()->getPrimaryKey());
        $associationKey = $this->getTargetMapper()->getAdapter()->getPrimaryKey();

        $joinClause = $this->getOption('joinClause', "$lookupTable.$associationForeignKey = $tableName.$associationKey");

        if (is_array($foreignKey)) {
            $query = $this->getMapper()->query();
            foreach ($foreignKey as $foreignKeyColumn) {
                $value = $this->getColumnValue($this->getSource(), $foreignKeyColumn);
                if (!$value) {
                    return false;
                }

                $query->where(array("$lookupTable.$foreignKeyColumn" => $value));
            }

        } else {
            $value = $this->getColumnValue($this->getSource(), $foreignKeyValueColumn);

            if (empty($value)) {
                return false;
            }

            $query = $this->getTargetMapper()->buildQuery();
            $query->where(array("$lookupTable.$foreignKey" => $value));
        }

        $query->join($lookupTable, $joinClause, array(), 'inner');

        if ($this->hasOption('order')) {
            $query->order($this->getOption('order'));
        }

        // $sql = new \Zend\Db\Sql\Sql($this->getTargetMapper()->getAdapter()->getDb());
        // echo $sql->getSqlStringForSqlObject($query);
        // exit;

        return $query;
    }

    public function loadData()
    {
        $query = $this->buildQuery();

        return $this->getTargetMapper()->fetchAll($query);
    }
}

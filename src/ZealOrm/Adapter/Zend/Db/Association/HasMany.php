<?php

namespace ZealOrm\Adapter\Zend\Db\Association;

use ZealOrm\Adapter\Zend\Db\Association\AbstractAssociation;

class HasMany extends AbstractAssociation
{
    public function getForeignKey()
    {
        if ($this->getOption('foreignKey')) {
            return $this->getOption('foreignKey');
        }

        $adapterOptions = $this->getSourceMapper()->getAdapterOptions();
        return $adapterOptions['primaryKey'];
    }

    public function buildQuery()
    {
        $query = $this->getTargetMapper()->buildQuery();

        $foreignKey = $this->getForeignKey();
        $primaryKey = $this->getOption('primaryKey', $foreignKey);

        $value = $this->getColumnValue($this->getSource(), $primaryKey);

        $query->where(array($foreignKey => $value));

        if ($this->hasOption('where')) {
            $query->where($this->getOption('where'));
        }

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

    public function saveData($object, $adapter)
    {

    }
}

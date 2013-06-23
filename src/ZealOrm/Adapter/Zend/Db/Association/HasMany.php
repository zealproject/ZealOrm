<?php

namespace ZealOrm\Adapter\Zend\Db\Association;

use ZealOrm\Adapter\Zend\Db\Association\AbstractAssociation;

class HasMany extends AbstractAssociation
{
    public function getForeignKey()
    {
        // TODO tidy this up with a better default
        $adapterOptions = $this->getSourceMapper()->getAdapterOptions();
        return $adapterOptions['primaryKey'];
    }

    public function buildQuery()
    {
        $query = $this->getTargetMapper()->buildQuery();

        $foreignKey = $this->getForeignKey();

        $query->where(array($foreignKey => $this->getColumnValue($this->getSource(), $foreignKey)));

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
}

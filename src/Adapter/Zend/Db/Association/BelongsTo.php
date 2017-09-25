<?php

namespace Zeal\Orm\Adapter\Zend\Db\Association;

use Zeal\Orm\Adapter\Zend\Db\Association\AbstractAssociation;

class BelongsTo extends AbstractAssociation
{
    public function getForeignKey()
    {
        // TODO tidy this up with a better default
        $adapterOptions = $this->getTargetMapper()->getAdapterOptions();
        return $adapterOptions['primaryKey'];
    }

    public function buildQuery()
    {
        $query = $this->getTargetMapper()->buildQuery();

        $foreignKey = $this->getForeignKey();

        if ($this->getOption('polymorphic')) {
            $query->where(array($foreignKey => $this->getColumnValue($this->getSource(), $this->getPolymorphicIdColumn())));

        } else {
            $query->where(array($foreignKey => $this->getColumnValue($this->getSource(), $foreignKey)));
        }

        // $sql = new \Zend\Db\Sql\Sql($this->getTargetMapper()->getAdapter()->getDb());
        // echo $sql->getSqlStringForSqlObject($query);
        // exit;

        return $query;
    }

    public function loadData()
    {
        $query = $this->buildQuery();

        return $this->getTargetMapper()->fetchObject($query);
    }

    public function saveData($object, $adapter)
    {
        // TODO
    }
}

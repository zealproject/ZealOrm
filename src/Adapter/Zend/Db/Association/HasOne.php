<?php

namespace Zeal\Orm\Adapter\Zend\Db\Association;

use Zeal\Orm\Adapter\Zend\Db\Association\AbstractAssociation;

class HasOne extends AbstractAssociation
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

        if (($as = $this->getOption('as')) !== null) {
            // this is the other side of a polymorphic association
            $query->where(array($as.'_type' => 'Collection'));

            $query->where(array($as.'_id' => $this->getColumnValue($this->getSource(), 'id')));

        } else {
            $foreignKey = $this->getForeignKey();

            $query->where(array($foreignKey => $this->getColumnValue($this->getSource(), $foreignKey)));
        }

        return $query;
    }

    public function loadData()
    {
        $query = $this->buildQuery();

        return $this->getTargetMapper()->fetchObject($query);
    }

    public function saveData($object, $adapter)
    {

    }
}

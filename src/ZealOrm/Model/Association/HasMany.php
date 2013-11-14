<?php

namespace ZealOrm\Model\Association;

use ZealOrm\Model\Association\AbstractAssociation;

class HasMany extends AbstractAssociation
{
    public function buildQuery()
    {
        $query = $this->getTargetMapper()->buildQuery();

        $foreignKey = $this->getForeignKey();

        $query->where(array($foreignKey => $this->getColumnValue($this->getSource(), $foreignKey)));

        return $query;
    }

    public function loadData()
    {
        $query = $this->buildQuery($sourceModel);

        return $this->getTargetMapper()->fetchAll($query);
    }

    public function saveData($object, $adapter)
    {
        // TODO
    }
}

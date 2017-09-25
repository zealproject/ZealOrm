<?php

namespace Zeal\Orm\Model\Association;

use Zeal\Orm\Model\Association\AbstractAssociation;

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
        $query = $this->buildQuery();

        return $this->getTargetMapper()->fetchAll($query);
    }

    public function saveData($object, $adapter)
    {
        // TODO
    }

    public function loadPaginatedData($currentPage, $itemsPerPage = 30)
    {

    }
}

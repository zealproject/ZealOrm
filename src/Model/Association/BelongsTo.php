<?php

namespace Zeal\Orm\Model\Association;

use Zeal\Orm\Model\Association\AbstractAssociation;

class BelongsTo extends AbstractAssociation
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

        return $this->getTargetMapper()->fetchObject($query);
    }

    public function saveData($object, $adapter)
    {
        // TODO
    }

    public function loadPaginatedData($currentPage, $itemsPerPage = 30)
    {

    }

    /**
     * Returns the foreign key column name
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->getOption('foreignKey');
    }
}
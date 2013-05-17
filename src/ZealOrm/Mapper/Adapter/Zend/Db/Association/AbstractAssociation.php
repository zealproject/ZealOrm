<?php

namespace ZealOrm\Mapper\Adapter\Zend\Db\Association;

use ZealOrm\Model\Association\AbstractAssociation as ModelAbstractAssociation;

abstract class AbstractAssociation extends ModelAbstractAssociation
{
    public function getTableName()
    {
        return $this->getTargetMapper()->getAdapter()->getOption('tableName');
    }

    public function getSourceColumnValue($source, $column)
    {
        if ($column == 'class') {
            return get_class($source);
        } else if ($column == 'classID') {
            $mapper = Zeal_Orm::getMapper($source);
            $primaryKey = $mapper->getAdapter()->getPrimaryKey();
            return $source->$primaryKey;
        } else {
            return $source->$column;
        }
    }
}
<?php

namespace ZealOrm\Adapter\Zend\Db\Association;

use ZealOrm\Model\Association\AbstractAssociation as ModelAbstractAssociation;
use ZealOrm\Orm;

abstract class AbstractAssociation extends ModelAbstractAssociation
{
    public function getTableName()
    {
        return $this->getTargetMapper()->getAdapter()->getOption('tableName');
    }

    public function getColumnValue($model, $column)
    {
        if ($column == 'classID') {
            $mapper = Orm::getMapper($model);
            $primaryKey = $mapper->getAdapter()->getPrimaryKey();
            return $model->$primaryKey;
        }

        return parent::getColumnValue($model, $column);
    }
}

<?php

namespace ZealOrm\Mapper\Adapter\Zend\Db\Association;

use ZealOrm\Model\Association\AbstractAssociation as ModelAbstractAssociation;

abstract class AbstractAssociation extends ModelAbstractAssociation
{
    public function getTableName()
    {
        return $this->getTargetMapper()->getAdapter()->getOption('tableName');
    }

    public function getColumnValue($model, $column)
    {
        if ($column == 'class') {
            return get_class($model);
        } else if ($column == 'classID') {
            $mapper = Zeal_Orm::getMapper($model);
            $primaryKey = $mapper->getAdapter()->getPrimaryKey();
            return $model->$primaryKey;
        } else {
            return $model->$column;
        }
    }
}
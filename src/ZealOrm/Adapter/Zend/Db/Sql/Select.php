<?php

namespace ZealOrm\Adapter\Zend\Db\Sql;

use Zend\Db\Sql\Select as ZendSelect;
use ZealOrm\Adapter\Query\QueryInterface;

class Select extends ZendSelect implements QueryInterface
{
    protected $platform;

    protected $primaryKey;

    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function __toString()
    {
        return $this->getSqlString($this->getPlatform());
    }

    public function setId($id, $params = null)
    {
        $this->where(array($this->getPrimaryKey(), $id));
    }

    public function getCacheKey()
    {
        // TODO
        return false;
    }
}

<?php

namespace ZealOrm\Adapter\Zend\Db\Sql;

use Zend\Db\Sql\Delete as ZendDelete;
use ZealOrm\Adapter\Query\QueryInterface;

class Delete extends ZendDelete implements QueryInterface
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
        // TODO
    }

    public function getCacheKey()
    {
        // TODO
        return false;
    }
}

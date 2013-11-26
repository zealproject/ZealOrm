<?php

namespace ZealOrm\Adapter\Zend\Db\Sql;

use Zend\Db\Sql\Select as ZendSelect;
use ZealOrm\Adapter\Query\QueryInterface;

class Select extends ZendSelect implements QueryInterface
{
    protected $platform;

    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function __toString()
    {
        return $this->getSqlString($this->getPlatform());
    }

    public function getCacheKey()
    {
        // TODO
        return false;
    }
}

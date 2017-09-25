<?php

namespace Zeal\Orm\Adapter\Zend\Db\Sql;

use Zend\Db\Sql\Select as ZendSelect;
use Zeal\Orm\Adapter\Query\QueryInterface;

class Select extends ZendSelect implements QueryInterface
{
    protected $platform;

    /**
     * @var string
     */
    protected $primaryKey;

    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Setter for the primary key
     *
     * @param string $primaryKey
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * Getter for the primary key
     *
     * @return string
     */
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
        $this->where(array($this->getPrimaryKey() => $id));
    }

    public function getCacheKey()
    {
        // TODO
        return false;
    }
}

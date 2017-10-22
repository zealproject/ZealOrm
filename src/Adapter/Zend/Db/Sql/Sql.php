<?php

namespace Zeal\Orm\Adapter\Zend\Db\Sql;

use Zend\Db\Sql\Sql as ZendSql;
use Zeal\Orm\Adapter\Query\QueryInterface;

class Sql extends ZendSql
{
    public function select($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }

        $select = new Select(($table) ?: $this->table);

        $select->setPlatform($this->getAdapter()->getPlatform());

        return $select;
    }

    public function delete($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        $delete = new Delete(($table) ?: $this->table);

        $delete->setPlatform($this->getAdapter()->getPlatform());

        return $delete;
    }
}
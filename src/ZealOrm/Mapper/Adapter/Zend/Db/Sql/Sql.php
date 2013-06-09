<?php

namespace ZealOrm\Mapper\Adapter\Zend\Db\Sql;

use Zend\Db\Sql\Sql as ZendSql;

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

        return new Select(($table) ?: $this->table);
    }
}
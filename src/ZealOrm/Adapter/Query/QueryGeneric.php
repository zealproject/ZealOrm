<?php

namespace ZealOrm\Adapter\Query;

use ZealOrm\Adapter\Query;

class QueryGeneric implements QueryInterface
{
    protected $params;

    /**
     * Returns a single param
     *
     * @param  string $key
     * @return mixed
     */
    public function getParam($key)
    {
        if (!array_key_exists($key, $this->params)) {
            return null;
        }

        return $this->params[$key];
    }

    /**
     * Returns all params
     *
     * @return array
     */
    public function getParams()
    {
        return $params;
    }

    public function where($params)
    {
        $this->params[] = $params;

        return $this;
    }
}

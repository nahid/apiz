<?php

namespace Apiz;

use Nahid\QArray\QueryEngine;

class QueryBuilder extends QueryEngine
{
    public function parseData($data)
    {
        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    public function readPath($path)
    {
        return [];
    }
}
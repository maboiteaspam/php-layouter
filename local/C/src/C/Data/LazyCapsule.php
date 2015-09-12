<?php

namespace C\Data;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Builder as QueryBuilder;

class LazyCapsule extends Capsule{
    /**
     * @param $query
     * @return LazyQuery
     */
    public static function query($query)
    {
        if ($query instanceof QueryBuilder) {
            return new LazyQuery($query);
        } else {
            // $query is a kind of raw value
            return new FakeQuery($query);
        }
    }
    /**
     * @param $query
     * @return LazyQuery
     */
    public static function autoTagged($query)
    {
        return self::query($query)->autoTag();
    }
}

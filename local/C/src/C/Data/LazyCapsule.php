<?php

namespace C\Data;

use C\Misc\Utils;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Builder as QueryBuilder;

class LazyCapsule extends Capsule{
    /**
     * @param $query
     * @return LazyQuery
     */
    public static function query($query)
    {
        return new LazyQuery($query);
    }
    /**
     * @param $query
     * @return LazyQuery
     */
    public static function autoTagged($query)
    {
        $lazyQuery = new LazyQuery($query);
        return $lazyQuery->autoTag();
    }
}

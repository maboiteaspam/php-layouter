<?php

namespace C\Data;

use C\Misc\Utils;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Eloquent extends Capsule{
    /**
     * @param $table
     * @param null $connection
     * @return EloquentBuilder
     */
    public static function delayed($table, $connection = null)
    {
        $connection = static::$instance->connection($connection);
        $processor = $connection->getPostProcessor();
        $query = new EloquentBuilder($connection, $connection->getQueryGrammar(), $processor);
        return $query->from($table);
    }

}

class EloquentBuilder extends QueryBuilder{

    public function byId($id, $columns = ['*'])
    {
        $obj = $this;
        return new TaggedData(function () use($id, $obj, $columns) {
            return Utils::objectToArray($obj->find($id, $columns));
        },function () use($obj, $columns) {
            return $obj->toSql();
        });
    }

    public function one($columns = ['*'])
    {
        $obj = $this;
        return new TaggedData(function () use($obj, $columns) {
            return Utils::objectToArray($obj->first($columns));
        },function () use($obj, $columns) {
            return $obj->toSql();
        });
    }

    public function all($columns = ['*'])
    {
        $obj = $this;
        return new TaggedData(function () use($obj, $columns) {
            return Utils::objectToArray($obj->get($columns));
        },function () use($obj, $columns) {
            return $obj->toSql();
        });
    }
}

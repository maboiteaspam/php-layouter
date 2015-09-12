<?php

namespace C\Data;

use C\Misc\Utils;
use Illuminate\Database\Query\Builder as QueryBuilder;

class LazyQuery{

    public $etagValue;
    /**
     * @var \Illuminate\Database\Query\Builder
     */
    public $query;

    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    public function __call($method, $args) {
        call_user_func_array([$this->query,$method], $args);
        return $this;
    }

    public function autoTag()
    {
        return $this->tagByUpdateDate(clone($this->query));
    }

    public function tagByUpdateDate(QueryBuilder $query)
    {
        return $this->setEtag($query->first(['updated_at']));
    }

    public function setEtag($etag)
    {
        $this->etagValue = $etag;
        return $this;
    }

    public function find($id, $columns = ['*']) {
        $query = $this->query;
        $that = $this;
        return new TaggedData(function () use(&$query, &$id, &$columns) {
            $res = Utils::objectToArray($query->find($id, $columns));
            return $res;
        },function () use(&$query, &$that) {
            if ($that->etagValue) {
                return $that->etagValue;
            }
            return $query->toSql();
        });
    }
    public function first($columns = ['*']) {
        $query = $this->query;
        $that = $this;
        return new TaggedData(function () use(&$query, &$columns) {
            $res = Utils::objectToArray($query->first($columns));
            return $res;
        },function () use(&$query, &$that) {
            if ($that->etagValue) {
                return $that->etagValue;
            }
            return $query->toSql();
        });
    }
    public function get($columns = ['*']) {
        $query = $this->query;
        $that = $this;
        return new TaggedData(function () use(&$query, &$columns) {
            $res = Utils::objectToArray($query->get($columns));
            return $res;
        },function () use(&$query, &$that) {
            if ($that->etagValue) {
                return $that->etagValue;
            }
            return $query->toSql();
        });
    }
}

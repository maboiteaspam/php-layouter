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
        $this->tagByUpdateDate(clone($this->query));
        return $this;
    }

    public function tagByUpdateDate(QueryBuilder $query)
    {
        $this->etagValue = $query->first(['updated_at']);
        return $this;
    }

    public function setEtag($etag)
    {
        $this->etagValue = $etag;
        return $this;
    }

    public function etag()
    {
        return $this->etagValue;
    }

    public function find($id, $columns = ['*']) {
        $query = $this->query;
        $that = $this;
        return new TaggedData(function () use(&$query, &$id, &$columns) {
            $res = Utils::objectToArray($query->find($id, $columns));
            return $res;
        },function () use(&$query, &$that) {
            if ($that->etag()) {
                return $that->etag();
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
            if ($that->etag()) {
                return $that->etag();
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
            if ($that->etag()) {
                return $that->etag();
            }
            return $query->toSql();
        });
    }
}

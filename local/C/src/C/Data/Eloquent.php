<?php

namespace C\Data;

use C\Misc\Utils;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Eloquent extends ViewData{

    public $unwrapMethod = '';
    public $unwrapArgs = [];

    /**
     * Wraps an Eloquent query.
     *
     * Note the real returned type is \C\Data\Eloquent
     * But for end user usage,
     * it is better to return a QueryBuilder.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public static function wrap($query)
    {
        if ($query instanceof QueryBuilder) {
            $data = new Eloquent($query);
            $data->etagWith(clone($query));
        } else {
            $data = parent::wrap($query);
        }
        return $data;
    }

    public function etagWith ($query) {
        $this->isTagged = false;
        $this->etag = $query;
        return $this;
    }

    public function getEtag () {
        if ($this->etag instanceof QueryBuilder) {
            if (!$this->isTagged) {
                $this->etag = $this->etag->first(['updated_at']);
                $this->isTagged = true;
            }
            return $this->etag;
        }
        return parent::getEtag();
    }

    public function find ($id, $columns = ['*']) {
        $this->unwrapMethod = 'find';
        $this->unwrapArgs = func_get_args();
        return $this;
    }

    public function first ($columns = ['*']) {
        $this->unwrapMethod = 'first';
        $this->unwrapArgs = func_get_args();
        return $this;
    }

    public function get ($columns = ['*']) {
        $this->unwrapMethod = 'get';
        $this->unwrapArgs = func_get_args();
        return $this;
    }

    public function max ($columns = ['*']) {
        $this->unwrapMethod = 'max';
        $this->unwrapArgs = func_get_args();
        return $this;
    }

    public function count ($columns = ['*']) {
        $this->unwrapMethod = 'count';
        $this->unwrapArgs = func_get_args();
        return $this;
    }

    public function min ($columns = ['*']) {
        $this->unwrapMethod = 'min';
        $this->unwrapArgs = func_get_args();
        return $this;
    }

    public function avg ($columns = ['*']) {
        $this->unwrapMethod = 'avg';
        $this->unwrapArgs = func_get_args();
        return $this;
    }

    public function sum ($columns = ['*']) {
        $this->unwrapMethod = 'sum';
        $this->unwrapArgs = func_get_args();
        return $this;
    }

    public function __call ($method, $args) {
        call_user_func_array([$this->data, $method], $args);
        return $this;
    }

    public function unwrap () {
        return Utils::objectToArray(
            call_user_func_array(
                [$this->data, $this->unwrapMethod],
                $this->unwrapArgs
            )
        );
    }

}

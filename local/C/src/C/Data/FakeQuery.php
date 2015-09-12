<?php

namespace C\Data;


class FakeQuery{

    public $some;

    public function __construct($some)
    {
        $this->some = $some;
    }

    public function __call($method, $args) {
        return $this;
    }

    public function find($id, $columns = ['*']) {
        $some = $this->some;
        return $some;
    }
    public function first($columns = ['*']) {
        $some = $this->some;
        return $some;
    }
    public function get($columns = ['*']) {
        $some = $this->some;
        return $some;
    }
}

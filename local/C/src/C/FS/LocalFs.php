<?php

namespace C\FS;

class LocalFs {

    public $calls = [];
    public function __construct( ) {
    }

    public function __call ($method, $args) {
        $calls[] = [$method, $args];
        return call_user_func_array($method, $args);
    }

}
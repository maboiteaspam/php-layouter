<?php

namespace C\FS;
use C\Misc\Utils;

class LocalFs {

    public $calls = [];
    public function __construct( ) {
    }

    public function __call ($method, $args) {
        $this->calls[] = [$method, $args, Utils::getStackTrace()];
        return call_user_func_array($method, $args);
    }

}
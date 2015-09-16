<?php

namespace C\FS;
use C\Misc\Utils;

class LocalFs {

    public static $record = false;
    public static $allcalls = [];
    public $calls = [];
    public function __construct( ) {
    }

    public function __call ($method, $args) {
        if (self::$record) {
            $k = [$method, $args, Utils::getStackTrace()];
            self::$allcalls[] = $k;
            $this->calls[] = $k;
        }
        return call_user_func_array($method, $args);
    }

    public static function __callStatic($method, $args) {
        if (self::$record) {
            self::$allcalls[] = [$method, $args, Utils::getStackTrace()];
        }
        return call_user_func_array($method, $args);
    }
}
<?php

namespace C\Data;

use C\Misc\Utils;

class Eloquent extends Wrapper{
    public static function __callStatic($name, $arguments) {
        $value = array_shift($arguments);
        return new TaggedData(function () use($name, $value) {
            return Utils::objectToArray($value->{$name}());
        },function () use($name, $value) {
            return $value->toSql();
        });
    }
}

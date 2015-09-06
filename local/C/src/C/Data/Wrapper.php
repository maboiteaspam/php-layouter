<?php

namespace C\Data;

class Wrapper{
    public static function po($value){
        return new TaggedData(function () use($value) {
            return $value;
        });
    }
    public static function __callStatic($name, $arguments) {
        $value = array_shift($arguments);
        return new TaggedData(function () use($name, $value, $arguments) {
            return $value->{$name}();
        });
    }
}

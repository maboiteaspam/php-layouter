<?php

namespace C\Data;

class Wrapper{
    public static function po($value){
        return new TaggedData(function () use(&$value) {
            return $value;
        });
    }
}

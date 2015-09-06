<?php

namespace C\Misc;

class Utils{

    public static function fileToEtag ($file) {
        if (is_string($file)) $file = [$file];
        $h = '-';
        foreach($file as $i=>$f){
            $h .= $i . '-';
            $h .= $f . '-';
            if (file_exists($f)) {
                $h .= filemtime($f) . '-';
            }
        }
        return $h;
    }

    public static function objectToArray($d) {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
        if (is_array($d)) {
            return array_map(__METHOD__, $d);
        }
        return $d;
    }

    public static function arrayPick ($arr, $pick) {
        if (count($pick)>0 && $arr) {
            $opts = [];
            foreach($pick as $n) {
                if (isset($arr[$n])) $opts[$n] = $arr[$n];
            }
            $arr = $opts;
        }
        return $arr;
    }

    public static function shorten ($path) {
        $path = realpath($path);
        if (substr($path, 0, strlen(getcwd()))===getcwd()) {
            $path = substr($path, strlen(getcwd())+1);
        }
        return $path;
    }

    public static function mergeMultiBlockOptions ($options, $defaults) {
        $options = array_merge($defaults, $options);
        foreach ($defaults as $n => $d) {
            $options[$n] = array_merge($d, $options[$n]);
        }
        return $options;
    }
}


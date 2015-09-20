<?php

namespace C\FS;
use C\Misc\Utils;

class LocalFs {

    public static $record = false;
    public static $allcalls = [];
    public $calls = [];
    public function __construct( ) {
    }

    public function recordCall ($method, $args) {
        if (self::$record) {
            $stack = Utils::getStackTrace();
            $this->calls[] = [$method, $args, $stack];
            self::recordAllCalls($method, $args, $stack);
        }
    }

    public function __call ($method, $args) {
        $this->recordCall($method, $args);
        return call_user_func_array($method, $args);
    }

    public static function recordAllCalls($method, $args, $stack=null) {
        if (self::$record) {
            self::$allcalls[] = [$method, $args, $stack?$stack:Utils::getStackTrace()];
        }
    }

    public static function __callStatic($method, $args) {
        self::recordAllCalls($method, $args);
        return call_user_func_array($method, $args);
    }

    /**
     * @param $file_path
     * @return bool
     */
    public static function file_exists($file_path){
        self::recordAllCalls(__FUNCTION__, func_get_args());
        return file_exists($file_path);
    }

    /**
     * @param $file_path
     * @return int
     */
    public static function filemtime($file_path){
        self::recordAllCalls(__FUNCTION__, func_get_args());
        return filemtime($file_path);
    }

    /**
     * @param $file_path
     * @return string
     */
    public static function realpath($file_path){
        self::recordAllCalls(__FUNCTION__, func_get_args());
        return realpath($file_path);
    }

    /**
     * @param $file_path
     * @param null $flags
     * @param null $context
     * @param null $offset
     * @param null $maxlen
     * @return string
     */
    public static function file_get_contents($file_path){
        self::recordAllCalls(__FUNCTION__, func_get_args());
        return call_user_func_array(__FUNCTION__, func_get_args());
    }

    /**
     * @param $file_path
     * @param $data
     * @param null $flags
     * @param null $context
     * @return int
     */
    public static function file_put_contents($file_path, $data){
        self::recordAllCalls(__FUNCTION__, func_get_args());
        return call_user_func_array(__FUNCTION__, func_get_args());
    }

    /**
     * @param $file_path
     * @return bool
     */
    public static function is_dir($file_path){
        self::recordAllCalls(__FUNCTION__, func_get_args());
        return is_dir($file_path);
    }

    /**
     * @param $file_path
     * @param null $time
     * @param null $atime
     * @return bool
     */
    public static function touch($file_path, $time=null, $atime=null){
        self::recordAllCalls(__FUNCTION__, func_get_args());
        return touch($file_path, $time, $atime);
    }

    /**
     * @param $file_path
     * @param int $mode
     * @param bool $recursive
     * @param null $content
     * @return bool
     */
    public static function mkdir($file_path){
        self::recordAllCalls(__FUNCTION__, func_get_args());
        return call_user_func_array(__FUNCTION__, func_get_args());
    }
}
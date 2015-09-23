<?php
namespace C\View;

class Context {

    public $helpers;
    public $knownData;

    public function __construct () {
        $this->helpers = [];
    }

    public function addHelper ($helper) {
        $this->helpers[] = $helper;
    }

    public function setKnownData ($knownData) {
        $this->knownData = $knownData;
    }

    public function __call($method, $args){
        foreach($this->helpers as $helper) {
            if (method_exists($helper, $method)) {
                return call_user_func_array([$helper, $method], $args);
            }
        }
        throw new \Exception("unknown function $method");
    }

}

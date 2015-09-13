<?php

namespace C\Data;

use Illuminate\Database\Capsule\Manager as Capsule;

class ViewData extends Capsule{

    public $data;
    public $etag = false;
    public $isTagged = false;

    /**
     * @param mixed $some
     * @return ViewData
     */
    public static function wrap($some)
    {
        $data = new ViewData($some);
        $data->etagWith($some);
        return $data;
    }

    public function __construct($some) {
        $this->data = $some;
        $this->isTagged = false;
    }

    public function etagWith ($some) {
        $this->etag = $some;
        $this->isTagged = false;
        return $this;
    }

    public function setEtag ($etag) {
        $this->etag = $etag;
        $this->isTagged = true;
        return $this;
    }

    public function getEtag () {
        if (!$this->isTagged) {
            $this->isTagged = true;
            try{
                $this->etag = sha1(json_encode($this->etag));
            }catch(\Exception $ex) {
                $this->etag = 'untaggable data';
            }
        }
        return $this->etag;
    }

    public function __call ($method, $args) {
        if (method_exists($this->data, $method)) {
            return call_user_func_array([$this->data, $method], $args);
        }
        return $this;
    }

    public function __get ($prop) {
        if (is_array($this->data)) {
            return $this->data[$prop];
        } else if(is_object($this->data)) {
            return $this->data->{$prop};
        }
        return null;
    }

    public function unwrap () {
        return $this->data;
    }
}

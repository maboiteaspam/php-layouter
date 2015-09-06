<?php

namespace C\Data;

class TaggedData{
    /**
     * @var Callable
     */
    public $getter;
    /**
     * @var Callable
     */
    public $tagger;
    public function __construct($getter, $tagger=null) {
        $this->getter = $getter;
        $this->tagger = $tagger?$tagger:$getter;
    }
    public function get() {
        $fn = $this->getter;
        return $fn();
    }
    public function etag() {
        $fn = $this->tagger;
        return sha1($fn());
    }
}

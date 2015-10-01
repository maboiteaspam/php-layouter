<?php

namespace C\Watch;

abstract class AbstractWatched implements WatchedInterface {

    public $name;
    public function setName ($name) {
        $this->name = $name;
    }
    public function getName () {
        return $this->name;
    }

}
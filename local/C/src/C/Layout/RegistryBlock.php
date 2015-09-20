<?php

namespace C\Layout;

class RegistryBlock{

    public $blocks = [];

    public function set ($id, Block $block){
        $this->blocks[$id] = $block;
    }

    /**
     * @param $id
     * @return Block
     */
    public function get ($id){
        if( isset($this->blocks[$id]))
            return $this->blocks[$id];
    }

    public function remove ($id){
        unset($this->blocks[$id]);
    }
}

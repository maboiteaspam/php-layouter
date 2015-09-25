<?php
namespace C\View;

use C\Layout\Block;

class Context {

    public $helpers = [];

    /**
     * @var Block
     */
    public $block;

    public function __construct () {
    }

    public function addHelper (ViewHelperInterface $helper) {
        $this->helpers[] = $helper;
    }

    public function prependHelper (ViewHelperInterface $helper) {
        array_unshift($this->helpers, $helper);
    }

    public function setBlockToRender (Block $block) {
        $this->block = $block;
        foreach($this->helpers as $helper) {
            /* @var ViewHelperInterface $helper */
            $helper->setBlockToRender($block);
        }
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

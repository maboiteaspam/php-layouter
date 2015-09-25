<?php
namespace C\View;

use C\Layout\Block;

abstract class AbstractViewHelper implements ViewHelperInterface{
    /**
     * @var Block
     */
    public $block;
    public function setBlockToRender ( Block $block) {
        $this->block = $block;
    }
    /**
     * @var Env
     */
    public $env;
    public function setEnv ( Env $env) {
        $this->env = $env;
    }

}
<?php
namespace C\View\Helper;

use C\Layout\Block;
use C\View\Env;

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
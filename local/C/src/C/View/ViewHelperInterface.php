<?php
namespace C\View;

use C\Layout\Block;

interface ViewHelperInterface {

    public function setBlockToRender ( Block $block);

}
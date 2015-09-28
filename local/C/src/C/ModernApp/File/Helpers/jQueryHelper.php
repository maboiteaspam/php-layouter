<?php
namespace C\ModernApp\File\Helpers;

use C\Layout\Layout;
use C\ModernApp\File\AbstractStaticLayoutHelper;

class jQueryHelper extends  AbstractStaticLayoutHelper{
    public function executeStructureNode (Layout $layout, $blockTarget, $nodeAction, $nodeContents) {
        if ($nodeAction==="dom_prepend_to") {

        } else if ($nodeAction==="dom_append_to") {

        } else if ($nodeAction==="dom_prepend_with") {

        } else if ($nodeAction==="dom_append_with") {

        } else if ($nodeAction==="dom_remove") {

        }
    }
}

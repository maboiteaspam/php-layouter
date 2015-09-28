<?php
namespace C\ModernApp\File;

use C\Layout\Layout;

abstract class AbstractStaticLayoutHelper implements StaticLayoutHelperInterface{

    public $baseDir = '';

    public function setStaticLayoutBaseDir ($baseDir) {
        $this->baseDir = $baseDir;
    }

    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents) {}

    public function executeStructureNode (Layout $layout, $blockTarget, $nodeAction, $nodeContents) {}
}

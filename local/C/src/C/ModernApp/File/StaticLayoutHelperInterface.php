<?php
namespace C\ModernApp\File;

use C\Layout\Layout;

interface StaticLayoutHelperInterface {

    public function setStaticLayoutBaseDir ($baseDir);

    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents);

    public function executeStructureNode (Layout $layout, $blockTarget, $nodeAction, $nodeContents);

}

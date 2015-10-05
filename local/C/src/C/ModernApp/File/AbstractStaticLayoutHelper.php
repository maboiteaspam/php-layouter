<?php
namespace C\ModernApp\File;

use C\Layout\Layout;

abstract class AbstractStaticLayoutHelper implements StaticLayoutHelperInterface{

    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents) {}

    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {}

    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContents) {}
}

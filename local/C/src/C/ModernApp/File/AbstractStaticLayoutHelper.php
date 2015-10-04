<?php
namespace C\ModernApp\File;

use C\Layout\Layout;

abstract class AbstractStaticLayoutHelper implements StaticLayoutHelperInterface{

    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents) {}

    /**
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return FileTransformsInterface
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        return false;
    }

    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContents) {}
}

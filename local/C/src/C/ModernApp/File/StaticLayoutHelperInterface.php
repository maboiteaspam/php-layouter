<?php
namespace C\ModernApp\File;

use C\Layout\Layout;

interface StaticLayoutHelperInterface {

    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents);

    /**
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return FileTransformsInterface
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents);

    public function executeBlockNode (FileTransformsInterface $T, $blockTarget, $nodeAction, $nodeContents);

}

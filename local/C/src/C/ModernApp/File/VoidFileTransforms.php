<?php
namespace C\ModernApp\File;

use C\Layout\VoidTransforms;

class VoidFileTransforms extends VoidTransforms implements FileTransformsInterface{

    public function executeMetaNode ($nodeAction, $nodeContents) {}

    /**
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return Transforms
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        return false;
    }

    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContents) {
    }

    public function getOptions() {
        return $this->innerTransform->getOptions();
    }

    public function then($fn) {
        return $this;
    }
}

<?php
namespace C\ModernApp\File\Helpers;

use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;

class FileHelper extends  AbstractStaticLayoutHelper{

    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if ($nodeAction==="import") {
            if (is_string($nodeContents)) {
                $nodeContents = [$nodeContents];
            }
            foreach ($nodeContents as $n) {
                $T->importFile($n);
            }
        }
        return false;
    }
}

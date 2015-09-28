<?php
namespace C\ModernApp\File\Helpers;

use C\FS\KnownFs;
use C\Layout\Layout;
use C\ModernApp\File\AbstractStaticLayoutHelper;

class AssetsHelper extends  AbstractStaticLayoutHelper{

    /**
     * @var KnownFs
     */
    protected $assetsFS;

    public function setAssetsFS (KnownFs $fs) {
        $this->assetsFS = $fs;
    }

    public function executeStructureNode (Layout $layout, $blockTarget, $nodeAction, $nodeContents) {
        if ($nodeAction==="add_assets") {

        } else if ($nodeAction==="remove_assets") {

        } else if ($nodeAction==="replace_assets") {

        } else if ($nodeAction==="relocate_assets") {

        }
    }
}

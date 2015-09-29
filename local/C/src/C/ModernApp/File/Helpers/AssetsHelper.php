<?php
namespace C\ModernApp\File\Helpers;

use C\Layout\Transforms;
use C\Layout\Layout;
use C\ModernApp\File\AbstractStaticLayoutHelper;

class AssetsHelper extends  AbstractStaticLayoutHelper{

    public function executeStructureNode (Layout $layout, $blockSubject, $nodeAction, $nodeContents) {
        if ($nodeAction==="add_assets") {
            Transforms::transform($layout)->addAssets($blockSubject, $nodeContents);

        } else if ($nodeAction==="remove_assets") {
            Transforms::transform($layout)->removeAssets($blockSubject, $nodeContents);

        } else if ($nodeAction==="replace_assets") {
            Transforms::transform($layout)->replaceAssets($blockSubject, $nodeContents);

        }
    }
}

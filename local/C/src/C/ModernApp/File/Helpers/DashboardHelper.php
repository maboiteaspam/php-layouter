<?php
namespace C\ModernApp\File\Helpers;

use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\Dashboard\Transforms as Dashboard;
use C\ModernApp\File\FileTransformsInterface;

class DashboardHelper extends  AbstractStaticLayoutHelper{

    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if ($nodeAction==="show_dashboard") {
            Dashboard::transform($T->getOptions())->show(__CLASS__);
        }
        return false;
    }
}

<?php
namespace C\ModernApp\File\Helpers;

use C\FS\KnownFs;
use C\Layout\Layout;
use C\Layout\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;

class LayoutHelper extends  AbstractStaticLayoutHelper{

    /**
     * @var KnownFs
     */
    protected $layoutFS;

    public function setLayoutFS (KnownFs $fs) {
        $this->layoutFS = $fs;
    }

    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents) {
        if ($nodeAction==="id") {
            $layout->setId($nodeContents);
        } else if ($nodeAction==="description") {
            $layout->setDescription($nodeContents);
        }
    }

    public function executeStructureNode (Layout $layout, $blockSubject, $nodeAction, $nodeContents) {
        if ($nodeAction==="set_template") {
            Transforms::transform($layout)->setTemplate($blockSubject, (string)$nodeContents);

        } else if ($nodeAction==="insert_before") {
            $nodeContents = array_merge([
                'target'=>'',
                'options'=>[],
            ],$nodeContents);
            Transforms::transform($layout)
                ->insertBeforeBlock($nodeContents['target'], $blockSubject, $nodeContents['options']);

        } else if ($nodeAction==="insert_after") {
            $nodeContents = array_merge([
                'target'=>'',
                'options'=>[],
            ],$nodeContents);
            Transforms::transform($layout)
                ->insertAfterBlock($nodeContents['target'], $blockSubject, $nodeContents['options']);

        } else if ($nodeAction==="clear") {
            Transforms::transform($layout)->clearBlock($blockSubject, 'all');

        } else if ($nodeAction==="delete") {
            Transforms::transform($layout)->deleteBlock($blockSubject);

        }
    }
}

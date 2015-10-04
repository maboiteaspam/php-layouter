<?php
namespace C\ModernApp\File\Helpers;

use C\Layout\Layout;
use C\Layout\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;
use Symfony\Component\Form\FormView;

class LayoutHelper extends  AbstractStaticLayoutHelper{

    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents) {
        if ($nodeAction==="id") {
            $layout->setId($nodeContents);
            return true;

        } else if ($nodeAction==="description") {
            $layout->setDescription($nodeContents);
            return true;
        }
        return !true;
    }

    public function executeBlockNode (FileTransformsInterface $T, $blockSubject, $nodeAction, $nodeContents) {
        if ($nodeAction==="set_template") {
            Transforms::transform($T->getOptions())
                ->setTemplate($blockSubject, (string)$nodeContents);
            return true;

        } else if ($nodeAction==="body") {
            Transforms::transform($T->getOptions())
                ->setBody($blockSubject, (string)$nodeContents);
            return true;

        } else if ($nodeAction==="set_default_data") {
            Transforms::transform($T->getOptions())
                ->sefDefaultData($blockSubject, $nodeContents);
            return true;

        } else if ($nodeAction==="update_meta") {
            Transforms::transform($T->getOptions())
                ->updateMeta($blockSubject, $nodeContents);
            return true;

        } else if ($nodeAction==="set_form") {
//            Transforms::transform($T->getOptions())
//                ->sefDefaultData($blockSubject, [
//                'form'=> new FormView()
//            ]);
            return true;

        } else if ($nodeAction==="insert_before") {
            if (is_string($nodeContents)) {
                $nodeContents = [
                    'target'    =>$nodeContents,
                    'options'   =>[],
                ];
            }
            $nodeContents = array_merge([
                'target'=>'',
                'options'=>[],
            ],$nodeContents);
            Transforms::transform($T->getOptions())
                ->insertBeforeBlock($nodeContents['target'], $blockSubject, $nodeContents['options']);
            return true;

        } else if ($nodeAction==="insert_after") {
            if (is_string($nodeContents)) {
                $nodeContents = [
                    'target'    =>$nodeContents,
                    'options'   =>[],
                ];
            }
            $nodeContents = array_merge([
                'target'    =>'',
                'options'   =>[],
            ],$nodeContents);
            Transforms::transform($T->getOptions())
                ->insertAfterBlock($nodeContents['target'], $blockSubject, $nodeContents['options']);
            return true;

        } else if ($nodeAction==="clear") {
            Transforms::transform($T->getOptions())->clearBlock($blockSubject, 'all');
            return true;

        } else if ($nodeAction==="delete") {
            Transforms::transform($T->getOptions())->deleteBlock($blockSubject);
            return true;

        }
        return !true;
    }
}

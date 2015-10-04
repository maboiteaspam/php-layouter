<?php
namespace C\ModernApp\File\Helpers;

use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;

class RequestHelper extends  AbstractStaticLayoutHelper{

    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if (substr($nodeAction, 0, strlen("for_device_"))==="for_device_") {
            $device = substr($nodeAction, strlen("for_device_"));
            return $T->forDevice($device);

        } else if (substr($nodeAction, 0, strlen("for_lang_"))==="for_lang_") {
            $lang = substr($nodeAction, strlen("for_lang_"));
            return $T->forLang($lang);

        }
        return false;
    }
}

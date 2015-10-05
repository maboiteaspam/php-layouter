<?php
namespace C\ModernApp\File\Helpers;

use C\Layout\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;

class IntlHelper extends  AbstractStaticLayoutHelper{

    public function executeBlockNode (FileTransformsInterface $T, $blockSubject, $nodeAction, $nodeContents) {
        if ($nodeAction==="add_intl") {
            if (is_string($nodeContents)) {
                $name = basename($nodeContents, '.yml');
                $name = explode('-', $name);
                if (count($name)===1) {
                    $domain = null;
                    $locale = $name[0];
                } else {
                    $domain = $name[0];
                    $locale = $name[1];
                }
                $nodeContents = [
                    'intl'      => $nodeContents,
                    'locale'    => $locale,
                    'domain'    => $domain,
                ];
            }
            $nodeContents = array_merge([
                'intl'      => '',
                'locale'    => '',
                'domain'    =>null,
            ],$nodeContents);
            Transforms::transform($T->getOptions())
                ->addIntl($blockSubject,
                    $nodeContents['intl'],
                    $nodeContents['locale'],
                    $nodeContents['domain']);

            return true;
        }
    }
}

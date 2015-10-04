<?php
namespace C\ModernApp\File;

interface FileTransformsInterface{
    public function getOptions();
    public function then($fn);
    public function forDevice($device);
    public function forLang($lang);
    public function executeMetaNode ($nodeAction, $nodeContent);
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContent);
    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContent);

}

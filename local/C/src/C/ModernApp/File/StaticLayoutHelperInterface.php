<?php
namespace C\ModernApp\File;

interface StaticLayoutHelperInterface {

    public function setStaticLayoutBaseDir ($baseDir);

    public function executeNode ($blockTarget, $nodeAction, $nodeContents);

}

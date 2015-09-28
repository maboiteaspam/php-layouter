<?php
namespace C\ModernApp\File;

abstract class AbstractStaticLayoutHelper implements StaticLayoutHelperInterface{

    public $baseDir = '';

    public function setStaticLayoutBaseDir ($baseDir) {
        $this->baseDir = $baseDir;
    }
}

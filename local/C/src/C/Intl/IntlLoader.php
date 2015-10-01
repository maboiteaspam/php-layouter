<?php

namespace C\Intl;

use Silex\Application;

class IntlLoader {

    /**
     * @var array
     */
    public $loaders = [];

    public function addLoader ($loader) {
        $this->loaders[] = $loader;
    }

    public function getLoader ($ext) {
        foreach ($this->loaders as $loader) {
            /* @var $loader AbstractIntlLoader */
            if ($loader->isExt($ext)) {
                return $loader;
            }
        }
    }

    public function clearCache () {
        foreach ($this->loaders as $loader) {
            /* @var $loader AbstractIntlLoader */
            $loader->clearCache();
        }
    }

    public function removeFile ($file, $ext) {
        return $this->getLoader($ext)->removeFile($file);
    }

    public function storeFile ($file, $ext) {
        return $this->getLoader($ext)->saveToCache($file);
    }

    public function loadFile ($file, $ext) {
        return $this->getLoader($ext)->loadFromCache($file);
    }

}

<?php

namespace C\Watch;

use C\Intl\IntlLoader;

class WatchedIntl extends WatchedRegistry {

    /**
     * @var IntlLoader
     */
    public $loader;

    public function setLoader (IntlLoader $loader) {
        $this->loader = $loader;
    }

    public function clearCache (){
        parent::clearCache();
        $this->loader->clearCache();
    }

    public function resolveRuntime () {
        parent::resolveRuntime();
    }

    public function build () {
        parent::build();
        $loader = $this->loader;
        $this->registry->each(function ($item) use($loader) {
            if($item['extension'])
                $loader->storeFile($item['absolute_path'], $item['extension']);
        });
        return $this;
    }

    /**
     * @return bool
     */
    public function loadFromCache () {
        return parent::loadFromCache();
    }

    /**
     * @return array
     */
    public function saveToCache () {
        return parent::saveToCache();
    }

    public function changed ($action, $file) {
        if ($action==='unlink'){
            $item = $this->registry->get($file);
            if ($item) {
                $this->loader->removeFile($item['absolute_path'], $item['extension']);
            }
        }

        parent::changed($action, $file);

        if($action==='change' || $action==='add' || $action==='addDir'){
            $item = $this->registry->get($file);
            if ($item) {
                $this->loader->storeFile($item['absolute_path'], $item['extension']);
            }
        }
    }

}
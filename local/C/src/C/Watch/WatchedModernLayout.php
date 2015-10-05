<?php

namespace C\Watch;

use C\ModernApp\File\Store;

class WatchedModernLayout extends WatchedRegistry {

    /**
     * @var Store
     */
    public $store;

    public function setStore (Store $store) {
        $this->store = $store;
    }

    public function clearCache (){
        parent::clearCache();
        $this->store->clearCache();
    }

    public function resolveRuntime () {
        parent::resolveRuntime();
    }

    public function build () {
        parent::build();
        $store = $this->store;
        $this->registry->each(function ($item) use($store) {
            $store->storeFile($item['absolute_path']);
        });
        return $this;
    }

    public function changed ($action, $file) {
        if ($action==='unlink'){
            $item = $this->registry->get($file);
            if ($item) {
                $this->store->removeFile($item['absolute_path']);
            }
        }

        parent::changed($action, $file);

        if($action==='change' || $action==='add' || $action==='addDir'){
            $item = $this->registry->get($file);
            if ($item) {
                $this->store->storeFile($item['absolute_path']);
            }
        }
    }

}
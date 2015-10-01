<?php

namespace C\Watch;

use C\Schema\Loader;

class WatchedCapsule extends WatchedRegistry {

    /**
     * @var Loader
     */
    public $schemaLoader;

    public function setSchemaLoader (Loader $schemaLoader) {
        $this->schemaLoader = $schemaLoader;
        $this->setRegistry($this->schemaLoader->registry);
    }


    public function resolveRuntime () {
        $this->schemaLoader->loadSchemas();
    }

    public function changed ($action, $file) {
        parent::changed($action, $file);
    }


}
<?php

namespace C\Schema;

use C\FS\Registry;
use C\FS\LocalFs;

class Loader implements ISchema{

    public $schemas = [];
    /**
     * @var \C\FS\Registry
     */
    public $registry;

    public function __construct(Registry $registry){
        $this->registry = $registry;
    }

    public function register(ISchema $schema){
        $this->schemas[] = $schema;
    }

    public function bootDb($settings, $env){
        if ($env==='dev') {
            if ($settings["driver"]==='sqlite') {
                if ($settings["database"]!==':memory:') {
                    $exists = LocalFs::file_exists($settings['database']);
                    if (!$exists) {
                        LocalFs::touch($settings["database"]);
                    }
                }
            }

            foreach( $this->schemas as $schema) {
                $this->registry->addClassFile($schema);
            }

            if ($settings["driver"]==='sqlite'
                && $settings["database"]===':memory:') {
                $this->createTables();
                $this->populateTables();
            } else {
                $this->refreshDb();
            }
        }
    }

    public function refreshDb(){
        if (!$this->registry->isFresh()) {
            $this->registry->clearFile();
            try{
                $this->dropTables();
            }catch(\Exception $ex){}
            $this->createTables();
            $this->populateTables();
        }
    }

    public function createTables(){
        foreach( $this->schemas as $schema) {
            /* @var $schema \C\Schema\ISchema */
            $schema->createTables();
        }
    }

    public function dropTables(){
        foreach( $this->schemas as $schema) {
            /* @var $schema \C\Schema\ISchema */
            $schema->dropTables();
        }
    }

    public function populateTables(){
        foreach( $this->schemas as $schema) {
            /* @var $schema \C\Schema\ISchema */
            $schema->populateTables();
        }
    }
}
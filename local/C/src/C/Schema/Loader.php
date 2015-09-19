<?php

namespace C\Schema;

use C\FS\Registry;
use C\FS\LocalFs;
use Illuminate\Database\Capsule\Manager as Capsule;

class Loader implements ISchema{

    public $schemas = [];
    /**
     * @var \C\FS\Registry
     */
    public $registry;
    public $capsule;

    public function __construct(Registry $registry){
        $this->registry = $registry;
    }

    public function register(ISchema $schema){
        $this->schemas[] = $schema;
    }

    public function setCapsule(Capsule $capsule){
        $this->capsule = $capsule;
    }

    public function loadSchemas(){
        $this->registry->loadFromFile();
        foreach( $this->schemas as $schema) {
            $this->registry->addClassFile($schema);
        }
    }

    public function refreshDb(){
        if (!$this->registry->isFresh()) {
            $this->registry->clearFile();
            $this->cleanDb();
            $this->initDb();
        }
    }

    public function cleanDb(){
        try{
            $this->dropTables($this->capsule);
        }catch(\Exception $ex){}
    }

    public function initDb(){
        $this->createTables($this->capsule);
        $this->populateTables($this->capsule);
    }

    public function createTables(Capsule $capsule){
        foreach( $this->schemas as $schema) {
            /* @var $schema \C\Schema\ISchema */
            $schema->createTables($capsule);
        }
    }

    public function dropTables(Capsule $capsule){
        foreach( $this->schemas as $schema) {
            /* @var $schema \C\Schema\ISchema */
            $schema->dropTables($capsule);
        }
    }

    public function populateTables(Capsule $capsule){
        foreach( $this->schemas as $schema) {
            /* @var $schema \C\Schema\ISchema */
            $schema->populateTables($capsule);
        }
    }
}
<?php

namespace C\Schema;

use C\FS\Registry;
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
        $this->registry->loadFromCache();
        foreach( $this->schemas as $schema) {
            $this->registry->addClassFile($schema);
        }
    }

    public function refreshDb(){
        if (!$this->registry->isFresh()) {
            $this->registry->clearCached();
            $this->cleanDb();
            $this->initDb();
        }
    }

    public function cleanDb(){
        try{
            $this->dropTables();
        }catch(\Exception $ex){}
    }

    public function initDb(){
        $this->createTables();
        $this->populateTables();
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
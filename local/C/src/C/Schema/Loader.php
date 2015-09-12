<?php

namespace C\Schema;

use C\FS\Registry;

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

    public function isFresh(){
        return $this->registry->signature===$this->registry->sign();
    }

    public function build(){

        $this->registry->signature = $this->registry->sign();
        foreach( $this->schemas as $schema) {
            /* @var $schema \C\Schema\ISchema */
            $schema->build();
            $reflector = new \ReflectionClass($schema);
            $this->registry->addItem($reflector->getFileName());
        }
    }

    public function populate(){
        foreach( $this->schemas as $schema) {
            /* @var $schema \C\Schema\ISchema */
            $schema->populate();
        }
    }
}
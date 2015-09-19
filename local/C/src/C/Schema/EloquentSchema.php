<?php
namespace C\Schema;

use Illuminate\Database\Capsule\Manager as Capsule;

abstract class EloquentSchema implements ISchema{
    public $capsule;
    public function __construct(Capsule $capsule) {
        $this->capsule = $capsule;
    }
}
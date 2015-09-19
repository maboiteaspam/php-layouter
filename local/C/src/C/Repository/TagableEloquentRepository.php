<?php

namespace C\Repository;

use Illuminate\Database\Capsule\Manager as Capsule;

abstract class TagableEloquentRepository extends TagableRepository{

    /**
     * @var \Illuminate\Database\Capsule\Manager
     */
    public $capsule;
    public function setCapsule(Capsule $capsule) {
        $this->capsule = $capsule;
    }

}
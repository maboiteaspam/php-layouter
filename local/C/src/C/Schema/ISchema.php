<?php

namespace C\Schema;
use Illuminate\Database\Capsule\Manager as Capsule;


interface ISchema {
    public function dropTables(Capsule $capsule);
    public function createTables(Capsule $capsule);
    public function populateTables(Capsule $capsule);
}
<?php

namespace C\Schema;


interface ISchema {
    public function dropTables();
    public function createTables();
    public function populateTables();
}

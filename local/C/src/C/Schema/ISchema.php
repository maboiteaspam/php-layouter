<?php

namespace C\Schema;


interface ISchema {
    public function build();
    public function populate();
}
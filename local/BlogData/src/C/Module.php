<?php

namespace C\BlogData;

class Module {
    public function register($app) {
        if (isset($app['schema_loader'])) {
            $app['schema_loader']->register(new Schema);
        }
    }
}
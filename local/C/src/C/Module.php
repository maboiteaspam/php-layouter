<?php

namespace C;

class Module {
    public function register($options) {
        if (isset($options['assetsFS'])) {
            $options['assetsFS']->register(__DIR__.'/Blog/assets/');
            $options['assetsFS']->register(__DIR__.'/DebugLayoutBuilder/assets/');
            $options['assetsFS']->register(__DIR__.'/jQueryLayoutBuilder/assets/');
        }
    }
}
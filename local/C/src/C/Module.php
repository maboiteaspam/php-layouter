<?php

namespace C;

class Module {
    public function register($options) {
        if (isset($options['templatesFS'])) {
            $options['templatesFS']->register(__DIR__.'/DebugLayoutBuilder/templates/');
            $options['templatesFS']->register(__DIR__.'/jQueryLayoutBuilder/templates/');
            $options['templatesFS']->register(__DIR__.'/HTMLLayoutBuilder/templates/');
        }
        if (isset($options['assetsFS'])) {
            $options['assetsFS']->register(__DIR__.'/DebugLayoutBuilder/assets/');
            $options['assetsFS']->register(__DIR__.'/jQueryLayoutBuilder/assets/');
        }
    }
}
<?php

namespace C\Blog;

class Module {
    public function register($options) {
        if (isset($options['assetsFS'])) {
            $options['assetsFS']->register(__DIR__.'/assets/');
        }
        if (isset($options['templatesFS'])) {
            $options['templatesFS']->register(__DIR__.'/templates/');
        }
    }
}
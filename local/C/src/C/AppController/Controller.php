<?php
namespace C\AppController;

class Controller{

    public $layout;
    public $app;

    public function __construct ($app, $layout) {
        $this->app = $app;
        $this->layout = $layout;
    }
}

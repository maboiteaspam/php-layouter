<?php
/* @var $this \C\View\ConcreteContext */

$this->display('html_begin');
    $this->display('html_head');
    $this->display('html_body_begin');
        $this->display('body');
        $this->display('footer');
    $this->display('html_body_end');
$this->display('html_end');

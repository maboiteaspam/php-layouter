<?php
/* @var $this \C\View\ConcreteContext */

// inline script on the foot position
$this->display('foot_inline_css');
$this->display('foot_inline_js');

// css import for template specifics, then page specifics
$this->display('template_footer_css');
$this->display('page_footer_css');
// js import for template specifics, then page specifics
$this->display('template_footer_js');
$this->display('page_footer_js');

// inline script on the last position
$this->display('last_inline_css');
$this->display('last_inline_js');

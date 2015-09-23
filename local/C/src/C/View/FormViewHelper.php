<?php
namespace C\View;

use C\Layout\Block;

// @todo import from SF/Silex.
abstract class FormViewHelper implements ViewHelperInterface {

    /**
     * @var Block
     */
    public $block;

    public function setBlock ( Block $block) {
        $this->block = $block;
    }


    // form
    // vendor/symfony/twig-bridge/Extension/FormExtension.php
    // vendor/symfony/twig-bridge/Resources/views/Form/form_div_layout.html.twig
    public abstract function form_widget();
    public abstract function form_errors();
    public abstract function form_label();
    public abstract function form_row();
    public abstract function form_rest();
    public abstract function form();
    public abstract function form_start();
    public abstract function form_end();
    public abstract function csrf_token();

}

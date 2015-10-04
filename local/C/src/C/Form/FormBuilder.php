<?php
namespace C\Form;

use \Symfony\Component\Form\Form;
use C\TagableResource\TagableResourceInterface;
use C\TagableResource\UnwrapableResourceInterface;
use C\TagableResource\TagedResource;

class FormBuilder implements TagableResourceInterface, UnwrapableResourceInterface {

    /**
     * @param Form $form
     * @return FormBuilder
     */
    public static function createView (Form $form) {
        $args = func_get_args();
        array_shift($args);
        return new self($form, $args);
    }

    /**
     * @var Form
     */
    public $form;
    /**
     * @var array
     */
    public $args;

    /**
     * @param Form $form
     * @param array $args
     */
    public function __construct (Form $form, $args=[]) {
        $this->form = $form;
        $this->args = $args;
    }

    /**
     * @param null $asName
     * @return TagedResource
     * @throws \Exception
     */
    public function getTaggedResource ($asName=null) {
        throw new \Exception("not taggable resource");
        $res = new TagedResource();
        return $res;
    }

    /**
     * @return mixed
     */
    public function unwrap () {
        return $this->form->createView();
    }
}
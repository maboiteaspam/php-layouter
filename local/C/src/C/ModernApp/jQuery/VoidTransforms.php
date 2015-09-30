<?php
namespace C\ModernApp\jQuery;

use C\Layout\Layout;
use C\Layout\TransformsInterface;

class VoidTransforms implements TransformsInterface{

    /**
     * @param TransformsInterface $transform
     */
    public function __construct(TransformsInterface $transform){
        $this->setLayout($transform->getLayout());
        $this->innerTransform = $transform;
    }
    public $innerTransform;

    /**
     * @var \C\Layout\Layout
     */
    public $layout;

    public function setLayout (Layout $layout) {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return Layout
     */
    public function getLayout () {
        return $this->layout;
    }
    public function forDevice ($device) {
        if ($this->layout->requestMatcher->isDevice($device)) {
            return $this->innerTransform;
        }
        return $this;
    }
    public function forRequest ($kind) {
        if ($this->layout->requestMatcher->isRequestKind($kind)) {
            return $this->innerTransform;
        }
        return $this;
    }
    public function forLang ($lang) {
        if ($this->layout->requestMatcher->isLang($lang)) {
            return $this->innerTransform;
        }
        return $this;
    }

    public function __call ($a, $b) {
        return $this;
    }
}

<?php
/* @var $this \C\View\ConcreteContext */

// note,

// it is not recommended
// to have javascript at this position
// but sometimes it helps to faster the rendering...

// for css, input template here, inline it,
// it should speed up the display for little price to pay regarding the content size.
?>
<head>
    <?php
    // inline script on the very first position
    $this->display('first_inline_css');
    $this->display('first_inline_js');

    // css import for template specifics, then page specifics
    $this->display('template_head_css');
    $this->display('page_head_css');
    // script import for template specifics, then page specifics
    $this->display('template_head_js');
    $this->display('page_head_js');

    // inline script on the top first position
    $this->display('head_inline_css');
    $this->display('head_inline_js');
    ?>
</head>

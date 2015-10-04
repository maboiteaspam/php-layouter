<?php
/* @var $this \C\View\ConcreteContext */
/* @var $comments array */
/* @var $title string Title of the right bar */
?>
<div class="right-bar">
    <?php $this->upper($title) ?>
    <?php $this->display('right-bar', true) ?>
</div>

<?php
/* @var $this \C\View\ConcreteContext */
?>
<div class="template-container">
    <div class="body-header">
        <?php $this->display('body_top'); ?>
    </div>
    <div class="body-content">
        <?php
        $this->display('body_content_left');
        $this->display('body_content');
        $this->display('body_content_right');
        ?>
    </div>
    <div class="body-footer">
        <?php $this->display('body_footer'); ?>
    </div>
</div>

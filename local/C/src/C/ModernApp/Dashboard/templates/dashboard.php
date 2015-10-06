<?php
/* @var $this \C\View\ConcreteContext */
/* @var $comments array */
/* @var $comment stdClass */
?>
<div class="dashboard close">
    <div class="dashboard-handle"></div>
    <div class="dashboard-content">
        <h2>Dashboard</h2>
        <div class="dashboard-body">
            <?php $this->display("dashboard-body", true); ?>
        </div>
    </div>
</div>

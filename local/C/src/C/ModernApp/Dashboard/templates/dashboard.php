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
            <ul>
                <li>
                    <?php $this->display("dashboard-options"); ?>
                </li>
                <li>
                    <?php $this->display("dashboard-stats"); ?>
                </li>
                <li>
                    <?php $this->display("dashboard-layout"); ?>
                </li>
            </ul>
        </div>
    </div>
</div>

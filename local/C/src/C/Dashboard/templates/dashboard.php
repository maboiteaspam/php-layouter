<?php ?>

<div class="dashboard close">
    <div class="dashboard-handle"></div>
    <div class="dashboard-content">
        <h2>Dashboard</h2>
        <div class="dashboard-body">
            <ul>
                <li>
                    <?php $display("dashboard-options"); ?>
                </li>
                <li>
                    <?php $display("dashboard-stats"); ?>
                </li>
                <li>
                    <?php $display("dashboard-layout"); ?>
                </li>
            </ul>
        </div>
    </div>
</div>
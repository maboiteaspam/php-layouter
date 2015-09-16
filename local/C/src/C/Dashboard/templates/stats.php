<?php ?>

<b class="dashboard-title">Stats</b>

<div class="dashboard-block-content">
    <div>
        <?php foreach(\C\FS\LocalFs::$allcalls as $call) { ?>
            <?php echo $call[0]."( ".$call[1][0]. " )"; ?><br/>
            <?php foreach($call[2] as $met){; ?>
                <?php echo $met["line"]."=".$met["class"]."::".$met["function"].''; ?><br/>
            <?php } ?>
            <br/><br/>
        <?php } ?>
    </div>
</div>

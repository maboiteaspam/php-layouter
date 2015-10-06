<?php
/* @var $this \C\View\ConcreteContext */
$allcalls = \C\FS\LocalFs::$allcalls;
?>
<b class="dashboard-title">Stats</b>

<div class="dashboard-block-content">
    <div>
        <?php foreach($allcalls as $call) { ?>
            <?php echo $call[0]."( ".$call[1][0]. " )"; ?><br/>
            <?php foreach($call[2] as $met){; ?>
                <?php echo (isset($met["line"])?$met["line"]."=":'').(isset($met["class"])?$met["class"]."::":'').$met["function"].''; ?><br/>
            <?php } ?>
            <br/><br/>
        <?php } ?>
    </div>
</div>

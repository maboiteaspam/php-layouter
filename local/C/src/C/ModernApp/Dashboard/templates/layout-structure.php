<?php
$struct = $struct();
?>

<b class="dashboard-title">Layout structure</b>
<div class="dashboard-block-content">
    <?php foreach ($struct as $blockPath=>$blockInfo) { ?>
        <div class="layout-block">
            <div>
                <span class="layout-block-path"><?php echo $blockPath; ?></span>
                -
                <span class="enable-help" target="<?php echo $blockInfo['id']; ?>">
                    <span class="enabled-text">Disable help</span>
                    <span class="disabled-text">Enable help</span>
                </span>
            </div>
            <div class="layout-block-meta">
                <div class="layout-block-meta-content">

                    <h5>Meta</h5>
                    <table>
                        <tr>
                            <td>id</td>
                            <td><?php echo $blockInfo['id']; ?></td>
                        </tr>
                        <tr>
                            <td>Shown</td>
                            <td><?php echo $blockInfo['shown']?'Yes':'no'; ?></td>
                        </tr>
                        <tr>
                            <td>Exists</td>
                            <td><?php echo $blockInfo['exists']?'Yes':'no'; ?></td>
                        </tr>
                    </table>

                    <h5>Template</h5>
                    <?php echo $blockInfo['template']; ?>

                    <?php if(count($blockInfo['assets'])) { ?>
                        <h5>Assets</h5>
                        <?php foreach( $blockInfo['assets'] as $assetGroup=>$assets) { ?>
                            <h5><?php echo $assetGroup; ?></h5>
                            <ul>
                                <?php foreach( $assets as $asset) { ?>
                                    <li><?php echo $asset; ?></li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

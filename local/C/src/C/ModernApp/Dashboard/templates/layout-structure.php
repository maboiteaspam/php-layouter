<?php
/* @var $this \C\View\ConcreteContext */
/* @var $serialized array */
/* @var $comment stdClass */

$blocks = $serialized['blocks'];
$layoutData = $serialized['layout'];
?>
<b class="dashboard-title">Layout structure</b>
<div class="dashboard-block-content">
    <?php foreach ($blocks as $blockPath=>$blockInfo) { ?>
        <div class="layout-block">
            <div class="block-head">
                <?php echo $blockInfo['isCacheable']?'✓':'✖'; ?>
                |<?php echo str_repeat("-", substr_count($blockPath, '/')-1); ?>&rsaquo;
                <span class="layout-block-path"><?php echo $blockInfo['id']; ?></span>
                <div class="block-head-tools">
                    <?php if (count($blockInfo['data'])) { ?>
                        <b>D</b>
                        -
                    <?php } ?>
                    <?php if (count($blockInfo['assets'])) { ?>
                        <b>A</b>
                        -
                    <?php } ?>
                    <span class="enable-help" target="<?php echo $blockInfo['id']; ?>">
                        <span class="enabled-text">mask</span>
                        <span class="disabled-text">reveal</span>
                    </span>
                </div>
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

                    <?php if($blockInfo['templateFile']) { ?>
                        <h5>Template File</h5>
                        <?php echo $blockInfo['templateFile']; ?>
                    <?php } ?>

                    <?php if(count($blockInfo['assets'])) { ?>
                        <h5>Assets</h5>
                        <?php foreach( $blockInfo['assets'] as $assetGroup=>$assets) { ?>
                            <h5><?php echo $assetGroup; ?></h5>
                            <ul>
                                <?php foreach( $assets as $asset) { ?>
                                    <li>
                                        <?php echo $asset['name']; ?>
                                        <br/>
                                        <?php echo $asset['path']; ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    <?php } ?>

                    <?php if(count($blockInfo['data'])) { ?>
                        <h5>Data</h5>
                        <ul>
                            <?php foreach( $blockInfo['data'] as $data) { ?>
                                <li>
                                    <b><?php echo $data['name']; ?></b>
                                    : <?php echo $data['value']; ?>
                                    <br/>
                                    <?php if(count($data['tags'])===1 && $data['tags'][0]['type']!=='po') { ?>
                                        tags:
                                        <ul>
                                            <?php foreach( $data['tags'] as $tag) { ?>
                                                <li>
                                                    type: <?php echo $tag['type']; ?>
                                                    <br>
                                                    value: <?php echo var_export($tag['value'], true); ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } else { ?>
                                        tag: PO
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>

                </div>
            </div>
        </div>
    <?php } ?>
</div>

<?php
namespace C\HTMLLayoutBuilder;

use C\LayoutBuilder\Transforms as BaseTransforms;
use C\Misc\Utils;
use C\Data\TaggedData;

class Transforms extends BaseTransforms{

    /**
     * @param mixed $app
     * @return Transforms
     */
    public static function transform ($app) {
        return new Transforms($app);
    }

    public function baseTemplate () {
        $this->setTemplate('root', __DIR__.'/templates/html.php');
        $this->set('html_begin', ['body'=>'<html>']);
        $this->setTemplate('head', __DIR__.'/templates/head.php');
        $this->set('body', [
            'options'=>[
                'template'=> __DIR__ . '/templates/1-column.php'
            ],
        ]);
        $this->setTemplate('footer', __DIR__.'/templates/footer.php');
        $this->set('script_bottom', ['body'=>'']);
        $this->set('html_end', ['body'=>'</html>']);
        return $this;
    }

    public function applyAssets(){
        $app = $this->app;

        $documentRoot = $app['documentRoot'];
        $basePath = $app['public_build_dir'];
        $assetsFS = $app['assetsFS'];
        $concat = $app['assets.concat'];

        $blockAssets = [];
        foreach ($this->layout->registry->blocks as $block) {
            foreach ($block->assets as $target=>$assets) {
                if (!isset($blockAssets[$target])) {
                    $blockAssets[$target] = [];
                }
                $blockAssets[$target] = array_merge($blockAssets[$target], $assets);
            }
        }

        if (count($blockAssets)) {

            if ($concat) {
                if (!is_dir($basePath)) mkdir($basePath, 0700, true);
            }

            foreach ($blockAssets as $target => $assets) {
                $targetBlock = $this->layout->getOrCreate($target);
                if ($targetBlock) {
                    $filesContent = [];
                    preg_match("/(css|js)$/", $target, $matches);
                    $ext = $matches[1];

                    $targetBlock->body .= "\n";

                    if ($concat) {

                        $h = '';
                        foreach ($assets as $i=>$asset) {
                            $a = $assetsFS->get($asset);
                            if ($a) {
                                $h .= $i . '-';
                                $h .= $asset . '-';
                                $h .= $a['sha1'] . '-';
                            }
                        }
                        $h = sha1($h.Utils::fileToEtag(__FILE__));

                        $concatAssetName = "$target-$h.$ext";
                        $concatAssetFile = $basePath . $concatAssetName;
                        $concatAssetUrl = substr($basePath, strlen($documentRoot)) . $concatAssetName;

                        if (!file_exists($concatAssetFile)) {
                            foreach ($assets as $asset) {
                                if ($assetsFS->file_exists($asset)) {
                                    $filesContent[$asset] = $this->readAndMakeAsset($assetsFS, $asset);
                                }
                            }
                            if ($ext==='js') $c = ";\n" . join(";\n", $filesContent) . "\n";
                            else $c = "" . join("\n", $filesContent) . "\n";
                            file_put_contents($concatAssetFile, $c);
                        }

                        if ($ext==='js')
                            $targetBlock->body .= sprintf(
                                '<script src="/%s" type="text/javascript"></script>', $concatAssetUrl);
                        else
                            $targetBlock->body .= sprintf(
                                '<link href="/%s" rel="stylesheet" />', $concatAssetUrl);

                    } else {
                        foreach ($assets as $asset) {
                            $a = $assetsFS->get($asset);
                            if ($a) {
                                $assetName = $a['dir'].$a['name'];
                                $assetUrl = "$assetName?t=".$a['sha1'];

                                if ($ext==='js')
                                    $targetBlock->body .= sprintf(
                                        '<script src="/%s" type="text/javascript"></script>', $assetUrl);
                                else
                                    $targetBlock->body .= sprintf(
                                        '<link href="/%s" rel="stylesheet" />', $assetUrl);

                                $targetBlock->body .= "\n";
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    public function readAndMakeAsset ($assetsFS, $assetFile){
        $content    = file_get_contents($assetFile);
        $assetFile  = $assetsFS->realpath($assetFile);
        $assetItem = $assetsFS->get($assetFile);
        $assetFile  = $assetItem['dir'].$assetItem['name'];
        if ($assetItem['extension']==='css') {
            $matches = [];
            preg_match_all('/url\s*\(([^)]+)\)/i', $content, $matches);
            foreach($matches[1] as $i=>$match){
                if (substr($match,0,1)==='"' || substr($match,0,1)==="'") {
                    $match = substr($match, 1, -1);
                }
                $content = str_replace($matches[0][$i], "url(/".$assetItem['dir']."/$match)", $content);
            }
            $content = "/* $assetFile */ \n$content";
        } else if ($assetItem['extension']==='js') {
            $content = "(function(modulePath){".$content."})('".$assetItem['dir']."');";
        }

        return $content;
    }


    public function updateEtags(){
        foreach($this->layout->registry->blocks as $block) {
            $h = '';
            $h .= $block->id . '-';
            if (isset($block->options['template'])) {
                $h .= Utils::fileToEtag($block->options['template']);
            }
            foreach($block->assets as $assets) {
                $h .= Utils::fileToEtag($assets);
            }
            array_walk_recursive($block->data, function($data) use(&$h){
                if ($data instanceof TaggedData) {
                    $h .= serialize($data->etag());
                } else {
                    try{
                        $h .= serialize($data);
                    }catch(\Exception $ex){

                    }
                }
            });
            $block->meta['etag'] = sha1($h);
        }
        return $this;
    }


    public function finalize () {
        $this->applyAssets()->updateEtags();
        return $this;
    }
}

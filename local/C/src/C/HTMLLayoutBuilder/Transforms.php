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
        $this->setTemplate('root',
            __DIR__.'/templates/html.php'
        )->set('html_begin', [
            'body'=>'<html>'
        ])->setTemplate('head',
            __DIR__.'/templates/head.php'
        )->set('body', [
            'options'=>[
                'template'=> __DIR__ . '/templates/1-column.php'
            ],
        ])->setTemplate('footer',
            __DIR__.'/templates/footer.php'
        )->set('script_bottom',  [
            'body'=>''
        ])->set('html_end',[
            'body'=>'</html>'
        ]);
        return $this;
    }

    public function applyAssets(){
        $app = $this->app;

        $app['dispatcher']->addListener('before_layout_render', function () use(&$app) {

            $documentRoot = $app['documentRoot'];
            $basePath = $app['public_build_dir'];
            $assetsFS = $app['assetsFS'];
            $env = $app['env'];
            $concat = $app['assets.concat'];

            $blockAssets = [];
            $blockToFile = [];
            foreach ($this->layout->registry->blocks as $block) {
                foreach ($block->assets as $target=>$assets) {
                    if (!isset($blockAssets[$target])) {
                        $blockAssets[$target] = [];
                    }
                    $blockAssets[$target] = array_merge($blockAssets[$target], $assets);
                }
            }

            if (count($blockAssets)) {

                if ($concat && $env==='dev' && !is_dir($basePath)) mkdir($basePath, 0700, true);

                foreach ($blockAssets as $target => $assets) {
                    $targetBlock = $this->layout->getOrCreate($target);
                    if ($targetBlock) {
                        preg_match("/(css|js)$/", $target, $matches);
                        $ext = strpos($target, 'js')===false?"css":"js";

                        $targetBlock->body .= "\n";

                        if ($concat) {

                            $h = '';
                            foreach ($assets as $i=>$asset) {
                                if ($assetsFS->file_exists($asset)) {
                                    $a = $assetsFS->get($asset);
                                    $h .= $i . '-';
                                    $h .= $asset . '-';
                                    $h .= $a['sha1'] . '-';
                                }
                            }

                            if ($this->app['debug']) $h = sha1($h.Utils::fileToEtag(__FILE__));
                            else $h = sha1($h);

                            $concatAssetName = "$target-$h.$ext";
                            $blockToFile[$target] = $basePath . $concatAssetName;
                            $concatAssetUrl = substr($basePath, strlen($documentRoot)) . $concatAssetName;

                            if ($ext==="js")
                                $targetBlock->body .= sprintf(
                                    '<script src="/%s" type="text/javascript"></script>', $concatAssetUrl);
                            else
                                $targetBlock->body .= sprintf(
                                    '<link href="/%s" rel="stylesheet" />', $concatAssetUrl);

                        } else {
                            foreach ($assets as $asset) {
                                if ($assetsFS->file_exists($asset)) {
                                    $a = $assetsFS->get($asset);
                                    $assetName = $a['dir'].$a['name'];
                                    $assetUrl = "$assetName?t=".$a['sha1'];

                                    if ($ext==="js")
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

                if ($concat) {
                    $app->after(function()use(&$assetsFS, &$blockAssets, &$blockToFile){

                        foreach ($blockAssets as $target => $assets) {
                            if (!file_exists($blockToFile[$target])) {
                                $filesContent = [];
                                foreach ($assets as $asset) {
                                    if ($assetsFS->file_exists($asset)) {
                                        $filesContent[$asset] = $this->readAndMakeAsset($assetsFS, $asset);
                                    }
                                }
                                if (strpos($target, 'js')!==false) $c = ";\n" . join(";\n", $filesContent) . "\n";
                                else $c = "" . join("\n", $filesContent) . "\n";
                                file_put_contents($blockToFile[$target], $c);
                            }
                        }
                    });
                }
            }
        });

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
        $app = $this->app;
        $assetsFS = $app['assetsFS'];
        $templatesFS = $app['templatesFS'];

        foreach($this->layout->registry->blocks as $e=>$block) {
            $h = '';
            $h .= $block->id . '-';
            if (isset($block->options['template'])) {
                $template = $block->options['template'];
                if ($templatesFS->file_exists($template)) {
                    $a = $templatesFS->get($template);
                    $h .= $e . '-';
                    $h .= $template . '-';
                    $h .= $a['sha1'] . '-';
                    $h = sha1($h);
                }
            }
            foreach($block->assets as $target=>$assets) {
                foreach($assets as $i=>$asset){
                    if ($assetsFS->file_exists($asset)) {
                        $a = $assetsFS->get($asset);
                        $h .= $target . '-';
                        $h .= $i . '-';
                        $h .= $asset . '-';
                        $h .= $a['sha1'] . '-';
                        $h = sha1($h);
                    }
                }
            }
            array_walk_recursive($block->data, function($data) use(&$h){
                if ($data instanceof TaggedData) {
                    $h .= serialize($data->etag());
                    $h = sha1($h);
                } else {
                    try{
                        $h .= serialize($data);
                        $h = sha1($h);
                    }catch(\Exception $ex){

                    }
                }
            });
            $block->meta['etag'] = $h;
        }
        return $this;
    }


    public function finalize () {
        $this->applyAssets()->updateEtags();
        return $this;
    }
}

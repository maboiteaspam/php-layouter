<?php
namespace C\HTMLLayoutBuilder;

use C\LayoutBuilder\Transforms as BaseTransforms;
use C\Misc\Utils;
use C\LayoutBuilder\Layout\Layout;
use C\LayoutBuilder\Layout\Block as BaseBlock;
use C\HTMLLayoutBuilder\Layout\Block as Block;

class Transforms extends BaseTransforms{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout) {
        return new Transforms($layout);
    }

    public function getOrCreate ($id){
        if (!($id instanceof BaseBlock)) {
            $block = $this->layout->registry->get($id);
            if (!$block) {
                $block = new Block($id);
                $this->layout->registry->set($id, $block);
            }
        } else {
            $block = $id;
        }
        return $block;
    }

    public function createBase($options=[]){
        $options = array_merge([
            'options'=>[
                'template'=> __DIR__ . '/templates/1-column.php'
            ],
        ], $options);
            $this->layout->setRoot([
                'options'=> [
                    'template'=>__DIR__.'/templates/html.php',
                ],
            ]);
            $this->set('html_begin', ['body'=>'<html>']);
            $this->set('head', [
                'options'=> [
                    'template' => __DIR__.'/templates/head.php',
                ],
            ]);
            $this->set('body', $options);
            $this->set('footer', [
                'options'=> [
                    'template' => __DIR__.'/templates/footer.php',
                ],
            ]);
            $this->set('script_bottom', ['body'=>'']);
            $this->set('html_end', ['body'=>'</html>']);
        return $this;
    }

    public function applyAssets($options=[]){
        $options = array_merge(['documentRoot'=>'www/', 'basePath'=>'assets/'], $options);

            $files = [];
            foreach ($this->layout->registry->blocks as $block) {
                foreach ($block->assets as $target=>$assets) {
                    if (!isset($files[$target])) {
                        $files[$target] = [];
                    }
                    $files[$target] = array_merge($files[$target], $assets);
                }
            }

            if (count($files)>0) {
                $documentRoot = $options['documentRoot'];
                $basePath = $options['basePath'];
                $assetsPath = $documentRoot.$basePath;

                foreach ($files as $target=>$assets) {
                    $targetBlock = $this->getOrCreate($target);

                    if ($targetBlock) {
                        preg_match("/(css|js)$/", $target, $matches);
                        $ext = $matches[1];
                        $h = Utils::fileToEtag(array_merge($assets, [__FILE__]));

                        $assetName = $target.'-'.sha1($h).'.'.$ext;
                        $assetFile = $assetsPath . $assetName;
                        $assetUrl = $basePath . $assetName;

                        if (!file_exists($assetFile)) {
                            $c = '';
                            foreach ($assets as $asset) {
                                $fc = file_get_contents($asset);
                                if ($ext==='js') $fc = ';'.$fc.';';
                                $c .= $fc."\n";
                            }
                            if (!is_dir($assetsPath)) mkdir($assetsPath, 0700, true);
                            file_put_contents($assetFile, $c);
                        }

                        $targetBlock->body .= "\n";
                        if ($ext==='js')
                            $targetBlock->body .= sprintf(
                                '<script src="/%s" type="text/javascript"></script>', $assetUrl);
                        else
                            $targetBlock->body .= sprintf(
                                '<link href="/%s" rel="stylesheet" />', $assetUrl);

                    }
                }
            }
        return $this;
    }
}

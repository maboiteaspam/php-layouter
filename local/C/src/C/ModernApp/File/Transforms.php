<?php
namespace C\ModernApp\File;

use C\FS\KnownFs;
use C\Layout\Transforms as BaseTransforms;
use C\FS\LocalFs;
use C\TagableResource\TagedResource;
use Moust\Silex\Cache\CacheInterface;
use Symfony\Component\Yaml\Yaml;

class Transforms extends BaseTransforms{

    /**
     * @var KnownFs
     */
    protected $modernFS;

    /**
     * @var CacheInterface
     */
    protected $cache;

    protected $helpers = [];

    public function addHelper (StaticLayoutHelperInterface $helper) {
        $this->helpers[] = $helper;
    }

    public function setModernLayoutFS (KnownFs $fs) {
        $this->modernFS = $fs;
    }

    public function setCache(CacheInterface $cache) {
        $this->cache = $cache;
    }

//    protected function resolveFilePath ($baseDir, $fileToResolve) {
//        $item = $this->templateFS->get($fileToResolve);
//        if ($item) {
//            return $item['absolute_path'];
//        }
//        if (substr($fileToResolve, 0, 1)==="/"
//            || preg_match("/[a-z]:[\\\]/i", substr($fileToResolve, 0, 3))>0) { // it s an absolute path, pass.
//            // nothing to do in current impl.
//        } else {
//            // let s assume their are relative to the path of the loaded YML file.
//            $fileToResolve = "$baseDir/$fileToResolve";
//        }
//        return $fileToResolve;
//    }

    public function buildFile ($filePath) {
        $layoutFile = $this->modernFS->get($filePath);
        if( $layoutFile===false) {
            throw new \Exception("File not found $filePath");
        }
        $layoutStruct   = Yaml::parse (LocalFs::file_get_contents ($filePath), true, false, true);
        $this->cache->store($layoutFile['dir'].'/'.$layoutFile['name'], $layoutStruct);
    }
    public function importFile ($filePath) {
        $layoutFile = $this->modernFS->get($filePath);
        if( $layoutFile===false) {
            throw new \Exception("File not found $filePath");
        }
        $ymlDir         = $layoutFile['dir'];
        $layoutStruct   = $this->cache->fetch($layoutFile['dir'].'/'.$layoutFile['name']);

        $resourceTag = new TagedResource();
        $resourceTag->addResource('layout', $layoutFile['absolute_path']);
        $this->layout->addGlobalResourceTag($resourceTag);

        foreach ($this->helpers as $helper) {
            /* @var $helper StaticLayoutHelperInterface */
            $helper->setStaticLayoutBaseDir($ymlDir);
        }

        if (isset($layoutStruct['meta'])) {
            foreach ($layoutStruct['meta'] as $nodeAction=>$nodeContent) {
                foreach ($this->helpers as $helper) {
                    /* @var $helper StaticLayoutHelperInterface */
                    $helper->executeMetaNode($this->layout, $nodeAction, $nodeContent);
                }
            }
        }

        if (isset($layoutStruct['structure'])) {
            foreach ($layoutStruct['structure'] as $subject=>$nodeActions) {
                foreach ($nodeActions as $nodeAction=>$nodeContent) {
                    foreach ($this->helpers as $helper) {
                        /* @var $helper StaticLayoutHelperInterface */
                        $helper->executeStructureNode($this->layout, $subject, $nodeAction, $nodeContent);
                    }
                }

            }
        }
//        // search paths, if they are relative, make them absolute.
//        foreach ($layoutStruct["structure"] as $blockId => $block) {
//            if (isset($block["set_template"])) {
//                $block["set_template"] = $this->resolveFilePath($ymlDir, $block["set_template"]);
//            }
//            if (isset($block["add_assets"])) {
//                foreach ($block["add_assets"] as $targetAssetsBlock => $assets) {
//                    foreach ( $assets as $i => $asset) {
//                        $assets[$i] = $this->resolveFilePath($ymlDir, $asset);
//                    }
//                }
//            }
//            if (isset($block["insert_before"])) {
//            }
//        }
//        // Definitely apply the static-file-layout to the Layout instance.
//        foreach ($layoutStruct["structure"] as $blockId => $block) {
//            if (isset($block["set_template"])) {
//                $this->setTemplate($blockId, $block["set_template"] );
//            }
//            if (isset($block["add_assets"])) {
//                $this->updateAssets($blockId, $block["add_assets"] );
//            }
//            if (isset($block["insert_before_block"])) {
//                $this->insertBeforeBlock($block["insert_before"], $blockId, []);
//            }
//        }
        return $this;
    }

}

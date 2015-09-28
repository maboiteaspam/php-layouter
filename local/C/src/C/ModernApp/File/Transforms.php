<?php
namespace C\ModernApp\File;

use C\FS\KnownFs;
use C\Layout\Transforms as BaseTransforms;
use C\FS\LocalFs;
use Moust\Silex\Cache\CacheInterface;
use Symfony\Component\Yaml\Yaml;

class Transforms extends BaseTransforms{

    /**
     * @var KnownFs
     */
    protected $staticLayoutFS;
    /**
     * @var KnownFs
     */
    protected $templateFS;
    /**
     * @var KnownFs
     */
    protected $assetsFS;

    /**
     * @var CacheInterface
     */
    protected $cache;

    protected $helpers = [];

    public function addHelper (StaticLayoutHelperInterface $helper) {
        $this->helpers[] = $helper;
    }

    public function setStaticLayoutFS (KnownFs $fs) {
        $this->staticLayoutFS = $fs;
    }

    public function setAssetsFS (KnownFs $fs) {
        $this->assetsFS = $fs;
    }

    public function setTemplateFS (KnownFs $fs) {
        $this->templateFS = $fs;
    }

    public function setCache(CacheInterface $cache) {
        $this->cache = $cache;
    }

    public function resolveFilePath ($baseDir, $fileToResolve) {
        $item = $this->templateFS->get($fileToResolve);
        if ($item) {
            return $item['absolute_path'];
        }
        if (substr($fileToResolve, 0, 1)==="/"
            || preg_match("/[a-z]:[\\\]/i", substr($fileToResolve, 0, 3))>0) { // it s an absolute path, pass.
            // nothing to do in current impl.
        } else {
            // let s assume their are relative to the path of the loaded YML file.
            $fileToResolve = "$baseDir/$fileToResolve";
        }
        return $fileToResolve;
    }

    public function loadFile ($filePath) {
        $layoutStruct = $this->cache->fetch($filePath);

        if (!$layoutStruct) {
            $layoutStruct   = Yaml::parse (LocalFs::file_get_contents ($filePath), true, false, true);
        }

        $ymlDir         = dirname ($filePath);
        // search paths, if they are relative, make them absolute.
        foreach ($layoutStruct["structure"] as $blockId => $block) {
            if (isset($block["set_template"])) {
                $block["set_template"] = $this->resolveFilePath($ymlDir, $block["set_template"]);
            }
            if (isset($block["add_assets"])) {
                foreach ($block["add_assets"] as $targetAssetsBlock => $assets) {
                    foreach ( $assets as $i => $asset) {
                        $assets[$i] = $this->resolveFilePath($ymlDir, $asset);
                    }
                }
            }
            if (isset($block["insert_before"])) {
            }
        }
        // Definitely apply the static-file-layout to the Layout instance.
        foreach ($layoutStruct["structure"] as $blockId => $block) {
            if (isset($block["set_template"])) {
                $this->setTemplate($blockId, $block["set_template"] );
            }
            if (isset($block["add_assets"])) {
                $this->updateAssets($blockId, $block["add_assets"] );
            }
            if (isset($block["insert_before_block"])) {
                $this->insertBeforeBlock($block["insert_before"], $blockId, []);
            }
        }
        return $this;
    }

}

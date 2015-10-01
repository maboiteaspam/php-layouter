<?php

namespace C\Intl;

use C\FS\KnownFs;
use C\Layout\Layout;
use C\Layout\Block;
use Silex\Application;

class IntlInjector {

    /**
     * @var KnownFS
     */
    public $intlFS;

    /**
     * @var \Silex\Translator;
     */
    public $translator;

    /**
     * @var IntlLoader
     */
    public $loader;

    public function loadFile ($file, $ext, $locale, $domain=null) {
        $extLoader = $this->loader->getLoader($ext);
        $content = $extLoader->loadFromCache($file);
//        var_dump($content);
        $this->translator->addResource(
            $extLoader->sfFmt(), $content, $locale, $domain);
    }


    public function applyToLayout (Layout $layout) {
        foreach ($layout->registry->blocks as $block) {
            /* @var $block Block */
            foreach ($block->intl as $intl) {
                $item = $this->intlFS->get($intl['item']);
                if ($item) {
                    $this->loadFile(
                        $item['absolute_path'],
                        $item['extension'],
                        $intl['locale'],
                        $intl['domain']
                    );
                }
            }
        }
    }

}

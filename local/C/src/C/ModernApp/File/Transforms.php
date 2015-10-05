<?php
namespace C\ModernApp\File;

use C\Layout\Transforms as BaseTransforms;
use C\Layout\TransformsInterface;
use C\ModernApp\File\Helpers\FileHelper;
use C\TagableResource\TagedResource;

class Transforms extends BaseTransforms implements FileTransformsInterface{

    /**
     * @param mixed $options
     * @return Transforms
     */
    public static function transform($options){
        $T = new self($options);
        $T->options = $options;
        $helpers = array_merge($options['modern.layout.helpers'],[
            new FileHelper()
        ]);
        return $T
            ->setStore($options['modern.layout.store'])
            ->setHelpers($helpers);
    }

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Store
     */
    protected $store;

    protected $helpers = [];

    public function addHelper (StaticLayoutHelperInterface $helper) {
        $this->helpers[] = $helper;
    }

    public function setStore(Store $store) {
        $this->store = $store;
        return $this;
    }

    public function getOptions() {
        return $this->options;
    }

    /**
     * @param $facets
     * @return $this|VoidFileTransforms
     */
    public function forFacets ($facets) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isFacets'],
            func_get_args())) {
            return $this;
        }
        return new VoidFileTransforms($this);
    }

    /**
     * switch to a device type
     * desktop, mobile, tablet
     * default is desktop
     *
     * @param $device
     * @return $this|VoidFileTransforms
     */
    public function forDevice ($device) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isDevice'],
            func_get_args())) {
            return $this;
        }
        return new VoidFileTransforms($this);
    }
    /**
     * switch to a request kind
     * ajax, get
     * default is get
     * esi-slave, esi-master are esi internals.
     * it can also receive negate kind such
     * !ajax !esi-master !esi-slave !get
     *
     * @param $kind
     * @return $this|VoidFileTransforms
     */
    public function forRequest ($kind) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isRequestKind'], func_get_args())) {
            return $this;
        }
        return new VoidFileTransforms($this);
    }
    public function forLang ($lang) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isLang'], func_get_args())) {
            return $this;
        }
        return new VoidFileTransforms($this);
    }

    public function setHelpers(array $helpers) {
        $this->helpers = [];
        foreach ($helpers as $helper) {
            $this->addHelper($helper);
        }
        return $this;
    }

    public function importFile ($filePath) {
        $layoutStruct = $this->store->get($filePath);

        $resourceTag = new TagedResource();
        $resourceTag->addResource($filePath, 'modern.layout');
        $this->layout->addGlobalResourceTag($resourceTag);

        if (isset($layoutStruct['meta'])) {
            foreach ($layoutStruct['meta'] as $nodeAction=>$nodeContent) {
                if (!$this->executeMetaNode($nodeAction, $nodeContent)) {
                    // mhh
                }
            }
        }

        $structure = Transforms::transform($this->options);
        if (isset($layoutStruct['structure'])) {
            foreach ($layoutStruct['structure'] as $actions) {

                foreach ($actions as $action => $options) {

                    $structure->then(function (FileTransformsInterface $T) use(&$structure, $action, $options) {

                        $sub = $this->executeStructureNode($structure, $action, $options);
                        if ($sub instanceof TransformsInterface) {
                            $structure = $sub;
                        } else if($sub===false) {
                            $subject = $action;
                            $nodeActions = $options;
                            $structure->then(function (FileTransformsInterface $T) use($subject, $nodeActions) {
                                foreach ($nodeActions as $nodeAction=>$nodeContent) {
                                    if (!$this->executeBlockNode($T, $subject, $nodeAction, $nodeContent)) {
                                        // mhh
                                    }
                                }
                            });
                        }
                    });
                }
            }
        }
        return $this;
    }

    public function executeMetaNode ($nodeAction, $nodeContent) {
        foreach ($this->helpers as $helper) {
            /* @var $helper StaticLayoutHelperInterface */
            if ($helper->executeMetaNode($this->layout, $nodeAction, $nodeContent)) {
                return true;
            }
        }
        return false;
    }

    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContent) {
        foreach ($this->helpers as $helper) {
            /* @var $helper StaticLayoutHelperInterface */
            $sub = $helper->executeStructureNode($T, $nodeAction, $nodeContent);
            if ($sub) return $sub;
        }
        return false;
    }

    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContent) {
        foreach ($this->helpers as $helper) {
            /* @var $helper StaticLayoutHelperInterface */
            if ($helper->executeBlockNode($T, $subject, $nodeAction, $nodeContent)) {
                return true;
            }
        }
        return false;
    }

}

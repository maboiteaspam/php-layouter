<?php

namespace C\Layout;

use C\TagableResource\TagableResourceInterface;
use C\TagableResource\TagedResource;

class RequestTypeMatcher implements TagableResourceInterface{

    /**
     * default|ajax|esi
     * @var string
     */
    public $requestKind;
    /**
     * mobile|desktop
     * @var string
     */
    public $deviceType;
    /**
     * fr|en|default
     * @var string
     */
    public $langPreferred;

    public function __construct (){
        $this->requestKind = 'get';
        $this->deviceType = 'desktop';
        $this->langPreferred = 'en';
    }

    public function setRequestKind($kind){
        $this->requestKind = $kind;
    }
    public function setDevice($device){
        $this->deviceType = $device;
    }
    public function setLang($lang){
        $this->langPreferred = $lang;
    }

    public function isRequestKind ($kind) {
        if (in_array('any', func_get_args()))
            return true;
        foreach (func_get_args() as $kind) {
            $negate = false;
            if (substr($kind,0,1)==='!') {
                $negate = true;
                $kind = substr($kind,1);
            }
            if (!$negate&&$kind===$this->requestKind) {
                return true;
            } else if ($negate&&$kind!==$this->requestKind) {
                return true;
            }
        }
        return false;
    }

    public function isDevice($device) {
        return in_array($this->deviceType, func_get_args())
        || in_array('any', func_get_args());
    }

    public function isLang($language) {
        return in_array($this->langPreferred, func_get_args())
        || in_array('any', func_get_args());
    }

    public function isFacets($facets) {
        if (isset($facets['device'])
            && !$this->isDevice($facets['device']))
            return false;
        if (isset($facets['lang'])
            && !$this->isLang($facets['lang']))
            return false;
        if (isset($facets['request'])
            && !$this->isRequestKind($facets['request']))
            return false;
        return true;
    }

    /**
     * @return TagedResource
     * @throws \Exception
     */
    public function getTaggedResource() {
        $res = new TagedResource();
        $res->addResource($this->requestKind);
        $res->addResource($this->deviceType);
        $res->addResource($this->langPreferred);
        return $res;
    }
}
<?php

namespace C\Layout;

use C\TagableResource\TagableResourceInterface;
use C\TagableResource\TagedResource;
use \Symfony\Component\HttpFoundation\Request;

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


    public function setRequest (Request $request) {

        $this->requestKind = 'get';
        if ($request->isXmlHttpRequest()) {
            $this->requestKind = 'ajax';
        }
        // @todo add esi slave rendering here

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
        return $this->requestKind===$kind;
    }

    public function isDevice($device) {
        return $this->deviceType===$device;
    }

    public function isLang($language) {
        return $this->langPreferred===$language;
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
<?php

namespace C\LayoutBuilder\Layout;

class TaggedResource implements \Serializable{

    public $originalTag = '';
    public $resources = [];

    public function addTaggedResource (TaggedResource $resource) {
        $this->resources = array_merge($this->resources, $resource->resources);
    }

    public function addResource ($type, $resource) {
        if (is_object($resource)
            && !($resource instanceof \Serializable))
            return false;
        $this->resources[] = ['type'=>$type, 'value'=>$resource];
        return true;
    }

    public function refreshSign ($sqlRun, $fsSign) {
        if (!count($this->resources)) return false;
        $this->originalTag = $this->sign($sqlRun, $fsSign);
        return true;
    }

    public function isFresh ($sqlRun, $fsSign) {
        return $this->originalTag&&$this->originalTag===$this->sign($sqlRun, $fsSign);
    }

    public function sign ($sqlRun, $fsSign) {
        $h = '';
        foreach($this->resources as $resource) {
            if ($resource['type']==='sql') {
                $h .= serialize($sqlRun($resource['value']));
            } else if ($resource['type']==='file') {
                $h .= serialize($fsSign($resource['value']));
            } else {
                $h .= serialize($resource['value']);
            }
        }
        return sha1($h);
    }

    public function serialize() {
        return serialize([
            'originalTag' => $this->originalTag,
            'resources' => $this->resources,
        ]);
    }
    public function unserialize($data) {
        $data = unserialize($data);
        $this->originalTag  = $data['originalTag'];
        $this->resources    = $data['resources'];
    }

}
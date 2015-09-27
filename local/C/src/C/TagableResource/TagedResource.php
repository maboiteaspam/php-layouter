<?php
namespace C\TagableResource;

class TagedResource implements \Serializable{

    public $originalTag = '';
    public $resources = [];

    public function addTaggedResource (TagedResource $resource) {
        $this->resources = array_merge($this->resources, $resource->resources);
    }

    public function addResource ($resource, $type='po') {
        if (is_object($resource) && !($resource instanceof \Serializable))
            throw new \Exception("not serializable object");
        $this->resources[] = [
            'value'=>$resource,
            'type'=>$type,
        ];
        return true;
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
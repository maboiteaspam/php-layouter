<?php
namespace C\TagableResource;

class TagedResource implements \Serializable{

    public $originalTag = '';
    public $resources = [];

    public function addTaggedResource (TagedResource $tags, $asName=null) {
        if ($asName!==null) {
            foreach ($tags->resources as $i=>$resource) {
                $tags->resources[$i]['asName'] = $asName;
            }
        }
        $this->resources = array_merge($this->resources, $tags->resources);
    }

    public function addResource ($resource, $type='po', $asName=null) {
        if (is_object($resource) && !($resource instanceof \Serializable)) {
            throw new \Exception("not serializable object");
        }
        $this->resources[] = [
            'value'=>$resource,
            'type'=>$type,
            'asName'=>$asName,
        ];
        return true;
    }

    public function getResourcesByName($name) {
        $resources = [];
        foreach ($this->resources as $resource) {
            if ($resource['asName']===$name) {
                $resources[] = $resource;
            }
        }
        return $resources;
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

    public static function __set_state($data) // As of PHP 5.1.0
    {
        $obj = new TagedResource;
        $obj->originalTag  = $data['originalTag'];
        $obj->resources    = $data['resources'];
        return $obj;
    }
}
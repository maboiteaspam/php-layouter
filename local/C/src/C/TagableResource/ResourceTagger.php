<?php
namespace C\TagableResource;

class ResourceTagger{

    public $taggers = [];

    /**
     * @var TagedResource
     */
    public $taggedResource;

    public function __construct () {
        $this->taggers['po'] = function ($value) {
            return $value;
        };
    }

    public function setTaggedResource (TagedResource $resource) {
        $this->taggedResource = $resource;
    }

    public function getTaggedResource () {
        return $this->taggedResource;
    }

    public function tagDataWith ($dataType, $computer) {
        $this->taggers[$dataType] = $computer;
    }

    public function isFresh () {
        $resource = $this->taggedResource;
        $originalTag = $resource->originalTag;
        return $originalTag&&$originalTag===$this->sign();
    }

    public function sign () {
        $resource = $this->taggedResource;
        $h = '';
        foreach ($resource->resources as $res) {
            $tagger = $res['type'];
            if (isset($this->taggers[$tagger])) {
                $computer = $this->taggers[$tagger];
                $value = $computer($res['value']);
                try{
                    $h .= serialize($value);
                }catch(\Exception $ex) {
                    echo $ex;
                }
            } else {
                throw new \Exception("Missing tag computer type '$tagger'");
            }
        }
        $resource->originalTag = sha1($h);
        return $resource->originalTag;
    }
}
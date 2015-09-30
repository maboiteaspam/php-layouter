<?php
namespace C\TagableResource;

class ResourceTagger{

    public $taggers = [];

    public function __construct () {
        $this->taggers['po'] = function ($value) {
            return $value;
        };
    }

    public function tagDataWith ($dataType, $computer) {
        $this->taggers[$dataType] = $computer;
    }

    public function isFresh (TagedResource $resource) {
        $originalTag = $resource->originalTag;
        return $originalTag&&$originalTag===$this->sign($resource);
    }

    public function sign (TagedResource $resource) {
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
<?php

namespace C\HttpCache;

use C\FS\KnownFs;
use Illuminate\Database\Capsule\Manager as Capsule;

class ResourceTagger{

    public $tagger = [];

    public function tagDataWith ($dataType, $computer) {
        $this->tagger[$dataType] = $computer;
    }

    public function isFresh (TaggedResource $resource) {
        return $resource->originalTag&&$resource->originalTag===$this->sign($resource);
    }

    public function sign (TaggedResource $resource) {
        if (!$resource->originalTag) {
            $h = '';
            foreach ($resource->resources as $res) {
                if ($res['type']==='sql') {
                    $computer = $this->tagger['sql'];
                    $h .= serialize($computer($res['value']));
                } else if ($res['type']==='file') {
                    $computer = $this->tagger['file'];
                    $h .= serialize($computer($res['value']));
                } else {
                    $h .= serialize($res['value']);
                }
            }
            $resource->originalTag = sha1($h);
        }
        return $resource->originalTag;
    }
}
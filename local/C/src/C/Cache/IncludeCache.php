<?php

namespace C\Cache;

use Moust\Silex\Cache\FileCache;

class IncludeCache extends FileCache
{
    static function isSupported()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        $filename = $this->getFileName($key);

        try{

            $content = include($filename);
            if ($content===null) return false;

            if ($this->isContentAlive($content, $filename)) {
                return $content['data'];
            }
            else {
                $this->delete($key);
            }
        }catch(\Exception $ex) {
            // @todo log file not exists
//            echo $ex;
//            dump($filename);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $var = null, $ttl = 0)
    {
        $content = array('data' => $var, 'ttl' => (int) $ttl);
        return (bool) file_put_contents($this->getFileName($key), "<?php return\n".var_export($content, true)."\n;");
    }
}

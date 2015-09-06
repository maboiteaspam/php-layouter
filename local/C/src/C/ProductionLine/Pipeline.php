<?php

namespace C\ProductionLine;

class Pipeline{

    protected $onData;
    protected $onFlush;
    protected $lines = [];

    public function __construct ($onData=null, $onFlush=null) {
        $this->onData = $onData ? $onData : function ($line, $chunk) {$line->push($chunk);};
        $this->onFlush = $onFlush ? $onFlush : function () {};
    }

    /**
     * @param Pipeline|callable $line
     * @return Pipeline
     */
    public function pipe($line) {
        if (!($line instanceof Pipeline)) {
            $line = Pipeline::passThrough($line);
        }
        $this->lines[] = $line;
        return $this;
    }

    /**
     * @param $line
     * @return $this
     */
    public function unpipe($line) {
        $this->lines = array_diff($this->lines, array($line));
        return $this;
    }

    /**
     * @param mixed $some
     */
    protected function push($some) {
        foreach($this->lines as $line) {
            $line->write($some);
        }
    }

    public function end($some) {
        if ($this->onFlush) {
            $onFlush = $this->onFlush;
            call_user_func_array($onFlush, [$this, $some]);
        }
        foreach($this->lines as $line) {
            $line->end($some);
        }
    }

    /**
     * @param mixed $some
     */
    public function write($some) {
        if ($this->onData) {
            call_user_func_array($this->onData, [$this, $some]);
        } else {
        }
    }

    #region this should probably belong to an EventEmitter trait
    protected $events = [];
    public function on($event, $then) {
        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }
        $this->events[$event][] = $then;
    }
    public function off($event, $then) {
        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }
        $this->events[$event] = array_diff($this->events[$event], array($then));
    }
    public function emit($event) {
        $args = func_get_args();
        array_shift($args);
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $handler) {
                call_user_func_array($handler, $args);
            }
        }
    }
    #endregion


    /**
     * @param null $onData
     * @param null $onFlush
     * @return Pipeline
     */
    public static function through ($onData=null, $onFlush=null) {
        return new Pipeline($onData, $onFlush);
    }

    /**
     * @param null $onData
     * @param null $onFlush
     * @return Pipeline
     */
    public static function passThrough ($onData=null, $onFlush=null) {
        return Pipeline::through(function ($line, $chunk) use($onData) {
            call_user_func_array($onData, [$chunk]);
            $line->push($chunk);
        }, $onFlush);
    }
}

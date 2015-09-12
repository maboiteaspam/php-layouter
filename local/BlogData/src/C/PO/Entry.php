<?php

namespace C\BlogData\PO;

class Entry{
    public $data = [];
    public function __construct() {
        $this->data = include(__DIR__ . '/../fixtures/blog-entries.php');
    }
    /**
     * @param array $data
     * @return int
     */
    public function insert($data) {
        $data['id'] = count($this->data);
        $this->data[] = $data;
        return $data['id'];
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Query\Builder
     */
    public function byId($id) {
        return $this->data[$id];
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function mostRecent() {
        return array_splice($this->data, 0, 20);
    }
}

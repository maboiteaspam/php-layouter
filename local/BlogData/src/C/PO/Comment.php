<?php

namespace C\BlogData\PO;

class Comment{
    public $data = [];
    public function __construct() {
        foreach (include(__DIR__ . '/../fixtures/blog-entries.php') as $entry) {
            $this->data = array_merge($this->data, $entry['comments']);
        }
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
    public function byEntryId($id) {
        $comments = [];
        foreach ($this->data as $comment) {
            if ((int)$comment['blog_entry_id']===(int)$id) {
                $comments[] = $comment;
            }
        }
        return $comments;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function mostRecent() {
        return array_splice($this->data,0,5);
    }
}

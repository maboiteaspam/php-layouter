<?php
namespace C\BlogData\PO;

use C\BlogData\CommentRepositoryInterface;
use C\Repository\TagableRepository;

class CommentRepository extends TagableRepository implements CommentRepositoryInterface {

    public $data = [];
    public function __construct() {
        foreach (include(__DIR__ . '/../fixtures/blog-entries.php') as $entry) {
            $this->data = array_merge($this->data, $entry->comments);
        }
    }

    function insert($data) {
        return 0;
    }

    /**
     * @return string
     */
    public function lastUpdateDate() {
        return $this->data[0]->updated_at;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function lastUpdatedByEntryId($id) {
        return $this->byEntryId($id)[0]->updated_at;
    }

    /**
     * @param $id
     * @param int $from
     * @param int $length
     * @return array
     */
    public function byEntryId($id, $from=0, $length=5) {
        $comments = [];
        foreach ($this->data as $comment) {
            if ((int)$comment->blog_entry_id===(int)$id) {
                $comments[] = $comment;
            }
        }
        return array_splice($comments, $from, $length);
    }

    /**
     * @param array $excludesEntries
     * @param int $page
     * @param int $by
     * @return array
     */
    public function mostRecent($excludesEntries=[], $page=0, $by=5) {
        $data = [];
        foreach( $this->data as $comment ) {
            if (!in_array($comment->blog_entry_id, $excludesEntries)) {
                $data[] = $comment;
            }
        }
        return array_splice(array_merge([], $data), $page*$by, $by);
    }
}

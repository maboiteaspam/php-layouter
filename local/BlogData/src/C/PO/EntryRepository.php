<?php
namespace C\BlogData\PO;

use C\BlogData\EntryRepositoryInterface;
use C\Repository\TagableRepository;

class EntryRepository extends TagableRepository implements EntryRepositoryInterface {

    public $data = [];
    public function __construct() {
        $this->data = include(__DIR__ . '/../fixtures/blog-entries.php');
    }

    /**
     * @return string
     */
    public function lastUpdateDate() {
        return $this->data[0]->updated_at;
    }

    /**
     * @param $data
     * @return int
     */
    function insert($data) {
        return 0;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function byId($id) {
        return $this->data[$id];
    }

    /**
     * @param int $from
     * @param int $length
     * @return array
     */
    public function mostRecent($from=0, $length=5) {
        return array_splice($this->data, $from, $length);
    }
}

<?php

namespace C\BlogData\Eloquent;

use Illuminate\Database\Capsule\Manager as Capsule;

class Entry{
    /**
     * @param array $data
     * @return int
     */
    public function insert($data) {
        return Capsule::table('blog_entry')->insertGetId($data);
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Query\Builder
     */
    public function byId($id) {
        return Capsule::table('blog_entry')->where('id', '=', $id)->take(1);
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function mostRecent() {
        return Capsule::table('blog_entry')->take(20)->orderBy('updated_at', 'DESC');
    }
}

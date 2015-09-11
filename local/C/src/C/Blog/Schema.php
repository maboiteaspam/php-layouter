<?php

namespace C\Blog;

use Illuminate\Database\Capsule\Manager as Capsule;

class Schema {
    public function setup() {
        $builder = Capsule::connection()->getSchemaBuilder();
        $builder->create('blog_entry', function($table) {
            $table->increments('id');
            $table->string('title');
            $table->string('author');
            $table->string('img_alt');
            $table->string('content');
            $table->enum('status', array('VISIBLE', 'HIDDEN'));
            $table->timestamps();
        });
        $builder->create('blog_comment', function($table) {
            $table->increments('id');
            $table->string('author');
            $table->string('content');
            $table->enum('status', array('VISIBLE', 'HIDDEN'));
            $table->timestamps();
            $table->integer('blog_entry_id');
        });
    }
    public function load() {
        $fixtureEntries = include(__DIR__ . '/fixtures/blog-entries.php');
        foreach ($fixtureEntries as $entry) {
            $comments = $entry['comments'];
            unset($entry['comments']);
            $id = Capsule::table('blog_entry')->insertGetId($entry);
            foreach ($comments as $comment) {
                $comment['blog_entry_id'] = $id;
                Capsule::table('blog_comment')->insert($comment);
            }
        }
    }
}

<?php

namespace C\BlogData;

use Illuminate\Database\Capsule\Manager as Capsule;
use \C\Schema\ISchema;

class Schema implements ISchema{
    public function build() {
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
    public function populate() {
        Capsule::connection()->transaction(function(){
            $fixtureEntries = include(__DIR__ . '/fixtures/blog-entries.php');
            foreach ($fixtureEntries as $entry) {
                $comments = $entry['comments'];
                unset($entry['comments']);
                unset($entry['id']);
                $id = Entry::insert($entry);
                foreach ($comments as $comment) {
                    unset($comment['id']);
                    $comment['blog_entry_id'] = $id;
                    Comment::insert($comment);
                }
            }
        });
    }
}

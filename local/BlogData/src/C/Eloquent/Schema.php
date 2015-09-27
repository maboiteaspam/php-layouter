<?php

namespace C\BlogData\Eloquent;

use \C\Schema\EloquentSchema;
use \Illuminate\Database\Schema\Blueprint;
use \C\BlogData\Eloquent\EntryRepository as Entry;
use \C\BlogData\Eloquent\CommentRepository as Comment;

class Schema extends  EloquentSchema{
    public function createTables() {
        $builder = $this->capsule->getConnection()->getSchemaBuilder();
        $builder->create('blog_entry', function(Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('author');
            $table->string('img_alt');
            $table->string('content');
            $table->enum('status', array('VISIBLE', 'HIDDEN'));
            $table->timestamps();
        });
        $builder->create('blog_comment', function(Blueprint $table) {
            $table->increments('id');
            $table->string('author');
            $table->string('content');
            $table->enum('status', array('VISIBLE', 'HIDDEN'));
            $table->timestamps();
            $table->integer('blog_entry_id');
        });
    }
    public function dropTables() {
        $builder = $this->capsule->getConnection()->getSchemaBuilder();
        $builder->drop('blog_entry');
        $builder->drop('blog_comment');
    }
    public function populateTables() {
        $capsule = $this->capsule;
        $this->capsule->getConnection()->transaction(function() use($capsule) {
            $entryModel = new Entry();
            $commentModel = new Comment();
            $entryModel->setCapsule($capsule);
            $commentModel->setCapsule($capsule);
            for ($i=0;$i<30;null) {
                $fixtureEntries = include(__DIR__ . '/../fixtures/blog-entries.php');
                foreach ($fixtureEntries as $entry) {
                    $comments = $entry->comments;
                    $entry->title = "#$i $entry->title";
                    unset($entry->blog_entry_id);
                    unset($entry->comments);
                    unset($entry->id);
                    $id = $entryModel->insert($entry);
                    foreach ($comments as $comment) {
                        unset($comment->id);
                        $comment->blog_entry_id = $id;
                        $commentModel->insert($comment);
                    }
                    $i++;
                }
            }
        });
    }
}

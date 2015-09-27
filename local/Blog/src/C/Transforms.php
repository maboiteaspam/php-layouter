<?php

namespace C\Blog;

use C\Layout\Transforms as BaseTransforms;
use C\Layout\Layout;

class Transforms extends BaseTransforms{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout){
        return new self($layout);
    }

    function home () {
        $this->setTemplate('body_content', 'Blog:/entry-list.php');
        $this->updateData('body_content', [
            'entries'=>[
                (object) [
                    'id'=>0,
                    'date'=> date('Y-m-d H:i:s'),
                    'author'=>'some',
                    'img_alt'=>'some',
                    'title'=>'some',
                    'content'=>'blog entry',
                    'comments'=>[],
                ],
                (object) [
                    'id'=>0,
                    'date'=> date('Y-m-d H:i:s'),
                    'author'=>'some',
                    'img_alt'=>'some',
                    'title'=>'some',
                    'content'=>'blog entry',
                    'comments'=>[],
                ],
            ],
        ]);
        $this->setTemplate('blog-entries-pagination', 'Blog:/pagination.php');
        $this->updateData('blog-entries-pagination', [
            'count'         => 2,
            'by'            => 5,
            'labelFormat'   => '%page%',
            'routeName'     => 'home',
            'routeParams'   => [],
        ]);
        $this->setTemplate('body_footer', 'Blog:/footer.php');
        $this->updateData('body_footer', [
            'year'=> date('Y'),
        ]);
        return $this;
    }
    function detail () {
        $this->setTemplate('body_content', 'Blog:/entry-detail.php');
        $this->updateData('body_content', [
            'entry'=> (object) [
                'id'=>0,
                'date'=> date('Y-m-d H:i:s'),
                'author'=>'some',
                'img_alt'=>'some',
                'title'=>'some',
                'content'=>'blog entry',
                'comments'=>[],
            ],
        ]);
        $this->updateData('body_content', [
            'entry'=> (object) [
                'id'=>0,
                'date'=> date('Y-m-d H:i:s'),
                'author'=>'some',
                'img_alt'=>'some',
                'title'=>'some',
                'content'=>'blog entry',
                'comments'=>[],
            ],
        ]);
        $this->setTemplate('blog_detail_comments', 'Blog:/entry-comments.php');
        $this->updateData('blog_detail_comments', [
            'comments'=> [],
        ]);
        $this->setTemplate('blog_form_comments', 'Blog:/form-comment.php');
        $this->updateData('blog_form_comments', [
            'form'=> null,
        ]);
        $this->setTemplate('body_footer', 'Blog:/footer.php');
        $this->updateData('body_footer', [
            'year'=> date('Y'),
        ]);
        return $this;
    }
}

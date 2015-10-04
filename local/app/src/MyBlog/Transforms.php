<?php

namespace MyBlog;

use C\Layout\Layout;
use C\Layout\Transforms as BaseTransforms;
use C\Blog\Transforms as BlogLayout;
use C\ModernApp\jQuery\Transforms as jQuery;
use C\ModernApp\Dashboard\Transforms as Dashboard;
use C\ModernApp\HTML\Transforms as HTML;
use Silex\Application;

class Transforms extends BaseTransforms{

    /**
     * @param Layout $layout
     * @return Transforms
     */
    public static function transform(Layout $layout){
        return new self($layout);
    }

    public function baseTemplate ($fromClass=__CLASS__,
                                  $fromFile=__FILE__) {
        $this->then(
            HTML::transform($this->layout)->baseTemplate()
        )->setTemplate('body_top',
            'MyBlog:/top.php'
        )->addIntl(
            'body_top', 'MyBlog:/en.yml', 'en'
        )->updateData('body_top', [
            'logo'=> '',
        ])->addAssets('body', [
            'template_head_css'=>[
                'MyBlog:/blog.css',
                'MyBlog:/template.css'
            ],
            'page_footer_js'=>[
                'MyBlog:/index.js'
            ],
        ])->insertAfterBlock('body_footer', 'extra_footer', [
            'body'=>'some'
        ])->then(
            $this->layout->debugEnabled
            ? Dashboard::transform($this->layout)->forRequest('get', 'esi-master')->show($fromClass)
            : null
        )->then(
            jQuery::transform($this->layout)->inject()
        );
        return $this;
    }

    public function home ($entries,
                          $latestComments,
                          $entriesCount,
                          $listEntryBy=5) {

        $this->then(
            BlogLayout::transform($this->layout)->home()

        );

        $this->updateData('body_content',[
            'entries'   => $entries
        ])->updateMeta('body_content',[
            'from'   => 'home'

        ]);

        $this->setTemplate('body_content_right', 'MyBlog:/right-bar.php'
        )->updateData('body_content_right',[
            'title' => 'Latest comments'
        ]);

        $this->insertAfterBlock('right-bar','rb_latest_comments'
        )->setTemplate('rb_latest_comments', 'Blog:/entry-comments.php'
        )->updateData('rb_latest_comments',[
            'comments'  => $latestComments

        ]);

        $this->updateData('blog-entries-pagination', [
            'count'         => $entriesCount,
            'by'            => $listEntryBy,
        ]);
        return $this;
    }

    public function detail ($entry, $comments, $latestComments) {

        $this->then(
            BlogLayout::transform($this->layout)->detail()
        );

        $this->updateData('body_content', [
            'entry'  => $entry,
        ])->updateMeta('body_content', [
            'from'      => 'blog_detail',
        ]);

        $this->updateData('blog_detail_comments', [
            'comments'  => $comments,
        ])->updateMeta('blog_detail_comments', [
            'from'      => 'blog_detail_comments',
        ]);

        $this->setTemplate('body_content_right',
            'MyBlog:/right-bar.php'
        )->updateData('body_content_right',[
            'title' => ''
        ]);

        $this->insertAfterBlock('right-bar',  'rb_latest_comments'
        )->setTemplate('rb_latest_comments', 'Blog:/entry-comments.php'
        )->updateData('rb_latest_comments', [
            'comments'  => $latestComments
        ])->updateMeta('rb_latest_comments', [
            'from'      => 'detail_rb',
        ]);

        return $this;
    }

}
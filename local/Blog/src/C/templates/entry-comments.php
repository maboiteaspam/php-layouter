<?php
/* @var $this \C\View\ConcreteContext */
/* @var $comments array */
/* @var $comment stdClass */
/* @var $title string */
?>
<div class="blog-comments">
    <h3><?php echo $this->upper($title) ?></h3>
    <?php foreach($comments as $comment){ ?>
        <div class="blog-comment">
            #<?php echo $comment->id; ?>
            -
            <?php echo $comment->author; ?>
            (<?php echo $comment->created_at; ?>)
            <br/>
            <?php echo $this->text_reduce($comment->content); ?>
            <br/>
        </div>
    <?php } ?>
</div>

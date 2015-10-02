<?php
/* @var $this \C\View\ConcreteContext */
/* @var $comments array */
/* @var $comment stdClass */
?>
<div class="blog-comments">
    <h3>Comments</h3>
    <?php foreach($comments as $comment){ ?>
        <div class="blog-comment">
            #<?php echo $comment->id; ?>
            -
            <?php echo $comment->author; ?>
            (<?php echo $comment->created_at; ?>)
            <br/>
            <?php echo $comment->content; ?>
        </div>
    <?php } ?>
</div>

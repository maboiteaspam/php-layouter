<div class="blog-comments">
    <h3>Comments</h3>
    <?php foreach($comments as $comment){ ?>
        <div class="blog-comment">
            <?php echo $comment->content; ?>
            <br/>
            <?php echo $comment->author; ?> <?php echo $comment->created_at; ?>
        </div>
    <?php } ?>
</div>

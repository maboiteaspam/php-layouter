<?php
/* @var $this \C\View\ConcreteContext */
/* @var $comments array */
/* @var $entry stdClass */
?>
<div class="blog-entry">
    <?php if ($entry) { ?>
        <h3>
            <a href="<?php echo $this->urlFor('blog_entry', $entry, ['id']).$this->urlArgs(); ?>">
                <?php echo $entry->title; ?>
            </a>
        </h3>
        <img src="<?php echo $this->urlAsset('blog_detail', $entry, ['id']); ?>"
             alt="<?php echo $entry->img_alt; ?>" />
        <div class="blog-content"><?php echo $entry->content; ?></div>
        <?php $this->display('blog_detail_comments'); ?>
    <?php } else { ?>
        No such blog entry !
    <?php } ?>
    <?php $this->display('blog_form_comments'); ?>
    <div class="blog-entry-footer">
        <a href="<?php echo $this->urlFor('home').$this->urlArgs(); ?>">return to home</a>
    </div>
</div>

<div class="blog-entry">
    <?php if ($entry) { ?>
        <h3>
            <a href="<?php echo $urlFor('blog_entry', $entry, ['id']).$urlArgs(); ?>">
                <?php echo $entry['title']; ?>
            </a>
        </h3>
        <img src="<?php echo $urlAsset('blog_detail', $entry, ['id']); ?>"
             alt="<?php echo $entry['img_alt']; ?>" />
        <div class="blog-content"><?php echo $entry['content']; ?></div>
        <?php $display('blog_detail_comments'); ?>
    <?php } else { ?>
        No such blog entry !
    <?php } ?>
    <?php $display('blog_form_comments'); ?>
    <div class="blog-entry-footer">
        <a href="<?php echo $urlFor('home').$urlArgs(); ?>">return to home</a>
    </div>
</div>

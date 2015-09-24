<?php
/* @var $this \C\View\ConcreteContext */
/* @var $entries array */
/* @var $entry stdClass */
?>
<div class="blog-entries">
    <?php foreach($entries as $entry){ ?>
        <div class="blog-entries-item">
            <h3>
                <a href="<?php echo $this->urlFor('blog_entry', $entry, ['id']).$this->urlArgs(); ?>">
                    <?php echo $entry->title; ?>
                </a> - by <?php echo $entry->author; ?>
                <span class="blog-entry-date"><?php echo $entry->created_at; ?></span>
            </h3>
            <div class="blog-content">
                <img src="<?php echo $this->urlAsset('blog_list', $entry, ['id']); ?>"
                     alt="<?php echo $entry->img_alt; ?>" />
                <?php echo $entry->content; ?>
            </div>
            <a href="<?php echo $this->urlFor('blog_entry', $entry, ['id']).$this->urlArgs(); ?>" class="read_more">
                read more
            </a>
        </div>
    <?php } ?>
</div>

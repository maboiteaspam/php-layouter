<div class="blog-entries">
    <?php foreach($entries as $entry){ ?>
        <div class="blog-entries-item">
            <h3>
                <a href="<?php echo $urlFor('blog_entry', $entry, ['id']).$urlArgs(); ?>">
                    <?php echo $entry->title; ?>
                </a> - by <?php echo $entry->author; ?>
                <span class="blog-entry-date"><?php echo $entry->created_at; ?></span>
            </h3>
            <div class="blog-content">
                <img src="<?php echo $urlAsset('blog_list', $entry, ['id']); ?>"
                     alt="<?php echo $entry->img_alt; ?>" />
                <?php echo $entry->content; ?>
            </div>
            <a href="<?php echo $urlFor('blog_entry', $entry, ['id']).$urlArgs(); ?>" class="read_more">
                read more
            </a>
        </div>
    <?php } ?>
</div>


<script type="text/javascript">
    $.get('<?php echo $url; ?>', function(data){
        data = $(data);
        if ($(data).first().is("c_block_node")) {
            data = $(data).first().children();
        }
        $('#<?php echo $id; ?>').replaceWith(data);
        $(document).trigger('c_block_loaded', '#<?php echo $target; ?>')
    });
</script>
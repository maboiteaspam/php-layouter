<script type="text/javascript">
    $.get('<?php echo $url; ?>', function(data){
        $('#<?php echo $id; ?>').replaceWith(data);
    });
</script>

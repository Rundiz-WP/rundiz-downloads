<?php
$pageParamLink = htmlspecialchars((string) filter_input(INPUT_GET, 'page'), ENT_QUOTES);
?>
<p>
    <?php 
    /* translators: %s filter_user_id Query string name. */
    printf(__('You can filter user\'s download items by adding the %s query string as name and the user id as value to the URL.', 'rd-downloads'), '<code>filter_user_id</code>'); 
    ?><br>
    <?php  _e('Example:', 'rd-downloads'); ?>
    <a href="<?php echo admin_url('admin.php?page=' . $pageParamLink . '&filter_user_id=1'); ?>"><?php echo admin_url('admin.php?page=' . $pageParamLink . '&filter_user_id=1'); ?></a>
</p>
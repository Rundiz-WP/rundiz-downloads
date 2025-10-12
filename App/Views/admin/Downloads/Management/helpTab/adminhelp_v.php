<?php
$pageParamLink = htmlspecialchars((string) filter_input(INPUT_GET, 'page'), ENT_QUOTES);
?>
<p>
    <?php 
    /* translators: %s filter_user_id Query string name. */
    printf(esc_html__('You can filter user\'s download items by adding the %s query string as name and the user id as value to the URL.', 'rd-downloads'), '<code>filter_user_id</code>'); 
    ?><br>
    <?php esc_html_e('Example:', 'rd-downloads'); ?>
    <a href="<?php echo admin_url('admin.php?page=' . $pageParamLink . '&filter_user_id=1');// phpcs:ignore ?>"><?php echo admin_url('admin.php?page=' . $pageParamLink . '&filter_user_id=1');// phpcs:ignore ?></a>
</p>
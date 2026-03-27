<?php
/**
 * Management page help tab admin.
 * 
 * @package rundiz-downloads
 */


if (!defined('ABSPATH')) {
    exit();
}


$rundiz_downloads_pageParamLink = htmlspecialchars((string) filter_input(INPUT_GET, 'page'), ENT_QUOTES);
?>
<p>
    <?php 
    /* translators: %s filter_user_id Query string name. */
    printf(esc_html__('You can filter user\'s download items by adding the %s query string as name and the user id as value to the URL.', 'rundiz-downloads'), '<code>filter_user_id</code>'); 
    ?><br>
    <?php esc_html_e('Example:', 'rundiz-downloads'); ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $rundiz_downloads_pageParamLink . '&filter_user_id=1')); ?>"><?php echo esc_url(admin_url('admin.php?page=' . $rundiz_downloads_pageParamLink . '&filter_user_id=1')); ?></a>
</p>
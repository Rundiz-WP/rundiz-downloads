<?php
/**
 * Logs page help tab admin.
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
    printf(esc_html__('You can filter the log for each user by adding the %s query string as name and the user id as value to the URL.', 'rundiz-downloads'), '<code>filter_user_id</code>'); 
    ?><br>
    <?php esc_html_e('Example:', 'rundiz-downloads'); ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $rundiz_downloads_pageParamLink . '&filter_user_id=1')); ?>"><?php echo esc_url(admin_url('admin.php?page=' . $rundiz_downloads_pageParamLink . '&filter_user_id=1')); ?></a>
</p>
<p>
    <?php 
    /* translators: %s filter_user_id Query string name. */
    printf(esc_html__('You can also filter the log for each download item by adding the %s query string as name and the download id as value to the URL.', 'rundiz-downloads'), '<code>filter_download_id</code>'); 
    ?><br>
    <?php esc_html_e('Example:', 'rundiz-downloads'); ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $rundiz_downloads_pageParamLink . '&filter_download_id=1')); ?>"><?php echo esc_url(admin_url('admin.php?page=' . $rundiz_downloads_pageParamLink . '&filter_download_id=1')); ?></a>
</p>
<p>
    <?php esc_html_e('The filters can be combined together.', 'rundiz-downloads'); ?><br>
    <?php esc_html_e('Example:', 'rundiz-downloads'); ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $rundiz_downloads_pageParamLink . '&filter_user_id=1&filter_download_id=1')); ?>"><?php echo esc_url(admin_url('admin.php?page=' . $rundiz_downloads_pageParamLink . '&filter_user_id=1&filter_download_id=1')); ?></a>
</p>
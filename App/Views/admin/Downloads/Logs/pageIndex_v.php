<?php
/**
 * Logs Listing page.
 * 
 * This page was copied from wp-admin/edit.php
 * 
 * @package rd-downloads
 */

/* @var $RdDownloadLogsListTable \RdDownloads\App\Models\RdDownloadLogsListTable */
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Download logs', 'rd-downloads'); ?></h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php esc_attr_e($form_result_class); ?> notice is-dismissible">
        <p>
            <strong><?php echo $form_result_msg;// phpcs:ignore ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.'); ?></span></button>
    </div>
    <?php } ?> 
    <div class="rd-downloads-form-result-placeholder"></div>

    <?php
    if (isset($RdDownloadLogsListTable) && is_object($RdDownloadLogsListTable)) {
        $RdDownloadLogsListTable->views();
    }
    ?> 
    <form id="rd-download-logs-list-items-form" class="rd-downloads-list-items-form" method="get">
        <input type="hidden" name="page" value="<?php echo (isset($_REQUEST['page']) ? esc_attr(sanitize_text_field(wp_unslash($_REQUEST['page']))) : ''); ?>">
        <?php 
        if (isset($_REQUEST['filter_user_id']) && !empty(trim($_REQUEST['filter_user_id']))) {// phpcs:ignore 
        ?>
        <input type="hidden" name="filter_user_id" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['filter_user_id']))); ?>">
        <?php 
        } 
        if (isset($_REQUEST['filter_download_id']) && !empty(trim($_REQUEST['filter_download_id']))) {// phpcs:ignore  
        ?>
        <input type="hidden" name="filter_download_id" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['filter_download_id']))); ?>">
        <?php 
        } 

        if (isset($RdDownloadLogsListTable) && is_object($RdDownloadLogsListTable)) {
            $RdDownloadLogsListTable->search_box(__('Search', 'rd-downloads'), 'rd-downloads');
            $RdDownloadLogsListTable->display();
        }
        ?> 
    </form>
</div><!--.wrap-->
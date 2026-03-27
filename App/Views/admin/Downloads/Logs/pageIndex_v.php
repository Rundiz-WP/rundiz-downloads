<?php
/**
 * Logs Listing page.
 * 
 * This page was copied from wp-admin/edit.php
 * 
 * @package rundiz-downloads
 * 
 * phpcs:disable WordPress.Security.NonceVerification.Recommended, Generic.WhiteSpace.ScopeIndent.Incorrect, Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */


if (!defined('ABSPATH')) {
    exit();
}


/* @var $RdDownloadLogsListTable \RundizDownloads\App\Models\RdDownloadLogsListTable */
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Download logs', 'rundiz-downloads'); ?></h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php echo esc_attr($form_result_class); ?> notice is-dismissible">
        <p>
            <strong><?php echo wp_kses_post($form_result_msg); ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'rundiz-downloads'); ?></span></button>
    </div>
    <?php } ?> 
    <div class="rundiz-downloads-form-result-placeholder"></div>

    <?php
    if (isset($RdDownloadLogsListTable) && is_object($RdDownloadLogsListTable)) {
        $RdDownloadLogsListTable->views();
    }
    ?> 
    <form id="rundiz-downloads-logs-list-items-form" class="rundiz-downloads-list-items-form" method="get">
        <input type="hidden" name="page" value="<?php echo (isset($_REQUEST['page']) ? esc_attr(sanitize_text_field(wp_unslash($_REQUEST['page']))) : ''); ?>">
        <?php 
        if (isset($_REQUEST['filter_user_id']) && !empty(trim(wp_unslash($_REQUEST['filter_user_id'])))) {// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        ?>
        <input type="hidden" name="filter_user_id" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['filter_user_id']))); ?>">
        <?php 
        } 
        if (isset($_REQUEST['filter_download_id']) && !empty(trim(wp_unslash($_REQUEST['filter_download_id'])))) {// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        ?>
        <input type="hidden" name="filter_download_id" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['filter_download_id']))); ?>">
        <?php 
        } 

        if (isset($RdDownloadLogsListTable) && is_object($RdDownloadLogsListTable)) {
            $RdDownloadLogsListTable->search_box(__('Search', 'rundiz-downloads'), 'rundiz-downloads');
            $RdDownloadLogsListTable->display();
        }
        ?> 
    </form>
</div><!--.wrap-->
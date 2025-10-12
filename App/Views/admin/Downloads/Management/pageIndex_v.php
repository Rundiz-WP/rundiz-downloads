<?php
/**
 * Downloads Listing page.
 * 
 * This page was copied from wp-admin/edit.php
 * 
 * @package rd-downloads
 */

/* @var $RdDownloadsListTable \RdDownloads\App\Models\RdDownloadsListTable */
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Downloads', 'rd-downloads'); ?> 
        <?php if (current_user_can('upload_files')) { ?>
        <a class="page-title-action" href="<?php echo admin_url('admin.php?page=rd-downloads_add');// phpcs:ignore ?>"><?php esc_html_e('Add New', 'rd-downloads'); ?></a>
        <?php }// endif; ?>
    </h1>

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
    if (isset($RdDownloadsListTable) && is_object($RdDownloadsListTable)) {
        $RdDownloadsListTable->views();
    }
    ?> 
    <form id="rd-downloads-list-items-form" class="rd-downloads-list-items-form" method="get">
        <input type="hidden" name="page" value="<?php echo (isset($_REQUEST['page']) ? esc_attr(sanitize_text_field(wp_unslash($_REQUEST['page']))) : ''); ?>">
        <?php 
        if (isset($_REQUEST['filter_user_id']) && !empty(trim($_REQUEST['filter_user_id']))) {// phpcs:ignore 
        ?>
        <input type="hidden" name="filter_user_id" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['filter_user_id']))); ?>">
        <?php 
        } 
        if (isset($_REQUEST['filter_download_type']) && !empty(trim($_REQUEST['filter_download_type']))) {// phpcs:ignore 
        ?>
        <input type="hidden" name="filter_download_type" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['filter_download_type']))); ?>">
        <?php 
        } 

        if (isset($RdDownloadsListTable) && is_object($RdDownloadsListTable)) {
            $RdDownloadsListTable->search_box(__('Search', 'rd-downloads'), 'rd-downloads');
            $RdDownloadsListTable->display();
        }
        ?> 
    </form>
</div><!--.wrap-->
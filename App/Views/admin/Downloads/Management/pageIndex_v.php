<?php
/**
 * Downloads Listing page.
 * 
 * This page was copied from wp-admin/edit.php
 */

/* @var $RdDownloadsListTable \RdDownloads\App\Models\RdDownloadsListTable */
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Downloads', 'rd-downloads'); ?> 
        <?php if (current_user_can('upload_files')) { ?>
        <a class="page-title-action" href="<?php echo admin_url('admin.php?page=rd-downloads_add'); ?>"><?php _e('Add New', 'rd-downloads'); ?></a>
        <?php }// endif; ?>
    </h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php echo $form_result_class; ?> notice is-dismissible">
        <p>
            <strong><?php echo $form_result_msg; ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.'); ?></span></button>
    </div>
    <?php } ?> 
    <div class="rd-downloads-form-result-placeholder"></div>

    <?php
    if (isset($RdDownloadsListTable) && is_object($RdDownloadsListTable)) {
        $RdDownloadsListTable->views();
    }
    ?> 
    <form id="rd-downloads-list-items-form" class="rd-downloads-list-items-form" method="get">
        <input type="hidden" name="page" value="<?php echo (isset($_REQUEST['page']) ? esc_attr(trim($_REQUEST['page'])) : ''); ?>">
        <?php if (isset($_REQUEST['filter_user_id']) && !empty(trim($_REQUEST['filter_user_id']))) { ?><input type="hidden" name="filter_user_id" value="<?php echo esc_attr($_REQUEST['filter_user_id']); ?>"><?php } ?> 
        <?php if (isset($_REQUEST['filter_download_type']) && !empty(trim($_REQUEST['filter_download_type']))) { ?><input type="hidden" name="filter_download_type" value="<?php echo esc_attr($_REQUEST['filter_download_type']); ?>"><?php } ?> 
        <?php
        if (isset($RdDownloadsListTable) && is_object($RdDownloadsListTable)) {
            $RdDownloadsListTable->search_box(__('Search', 'rd-downloads'), 'rd-downloads');
            $RdDownloadsListTable->display();
        }
        ?> 
    </form>
</div><!--.wrap-->
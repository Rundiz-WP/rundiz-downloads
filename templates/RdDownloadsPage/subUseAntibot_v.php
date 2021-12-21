<?php
/**
 * This template is based on "Bootstrap Basic 4" theme.
 * 
 * This page require Bootstrap 4.0, Font Awesome 5.3.0 CSS.
 * 
 * Available variables please look in `App\Controllers\Front\RdDownloadsPage` class `subUseAntibot()` method.
 * 
 * This page only visible if user agent has been blocked.
 */


get_header();

?>
<div id="main-column" class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
    <h1><?php _e('Human verification', 'rd-downloads'); ?></h1>

    <?php if (isset($form_result) && isset($form_result_msg)) { ?>
    <div class="alert alert-<?php echo ($form_result == 'success' ? 'success' : 'danger'); ?>" role="alert">
        <?php echo $form_result_msg; ?>
    </div>
    <?php } ?>

    <?php if (!isset($disableAntibotForm) || (isset($disableAntibotForm) && $disableAntibotForm === false)) { ?>
    <p><?php _e('Please enter the form below. Sorry for the inconvenient.', 'rd-downloads'); ?></p>
    <form id="rd-downloads-antibot-form" method="post">
        <fieldset class="border rounded p-2">
            <?php if (isset($downloadRow)) { ?> 
            <div class="mb-2 row">
                <label for="download_name" class="col-sm-3 col-form-label"><?php echo __('Downloads name', 'rd-downloads') ?></label>
                <div class="col-sm-9">
                    <input id="download_name" class="form-control-plaintext" type="text" readonly value="<?php esc_html_e($downloadRow->download_name); ?>">
                </div>
            </div>
            <div class="mb-2 row">
                <label for="download_size" class="col-sm-3 col-form-label"><?php echo __('File size', 'rd-downloads') ?></label>
                <div class="col-sm-9">
                    <input id="download_size" class="form-control-plaintext" type="text" readonly value="<?php echo str_replace('.00', '', size_format($downloadRow->download_size, 2)); ?>">
                </div>
            </div>
            <div class="mb-2 row">
                <label for="download_file_name" class="col-sm-3 col-form-label"><?php echo __('File name', 'rd-downloads') ?></label>
                <div class="col-sm-9">
                    <input id="download_file_name" class="form-control-plaintext" type="text" readonly value="<?php esc_html_e($downloadRow->download_file_name); ?>">
                </div>
            </div>
            <?php
            } else {
                trigger_error('$downloadRow is not defined.');
            }
            ?> 
        </fieldset>
        <div class="d-none" aria-hidden="true">
            <label for="<?php echo $honeypotName; ?>"><?php echo __('Please skip this field.', 'rd-downloads'); ?></label>
            <input id="<?php esc_attr_e($honeypotName); ?>" type="text" name="<?php esc_attr_e($honeypotName); ?>">
        </div>
        <div class="mb-3 form-check" aria-hidden="false">
            <input id="iamhuman" class="form-check-input" type="checkbox" name="iamhuman" value="1">
            <label class="form-check-label" for="iamhuman"><?php echo __('I\'m human.', 'rd-downloads'); ?></label>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fontawesome-icon icon-download fas fa-download"></i> <?php _e('Continue to download', 'rd-downloads'); ?></button>
    </form><!--#rd-downloads-antibot-form-->
    <?php } ?>
</div><!--#main-column-->

<!-- You can copy this template file from <?php echo __FILE__; ?> and put it in your theme to design it yours. For more information please read the readme file at <?php echo plugin_dir_path(RDDOWNLOADS_FILE) . 'templates'; ?> -->
<?php
get_footer();
?>
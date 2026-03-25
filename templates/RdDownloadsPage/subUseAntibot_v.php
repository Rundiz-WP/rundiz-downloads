<?php
/**
 * This template is based on "Bootstrap Basic 4" theme.
 * 
 * This page require Bootstrap 4.0, Font Awesome 5.3.0 CSS.
 * 
 * Available variables please look in `App\Controllers\Front\RdDownloadsPage` class `subUseAntibot()` method.
 * 
 * This page only visible if user agent has been blocked.
 * 
 * @package rundiz-downloads
 */


get_header();

?>
<div id="main-column" class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
    <h1><?php esc_html_e('Human verification', 'rundiz-downloads'); ?></h1>

    <?php if (isset($form_result) && isset($form_result_msg)) { ?>
    <div class="alert alert-<?php echo ('success' === $form_result ? 'success' : 'danger'); ?>" role="alert">
        <?php echo $form_result_msg;// phpcs:ignore ?>
    </div>
    <?php } ?>

    <?php if (!isset($disableAntibotForm) || (isset($disableAntibotForm) && false === $disableAntibotForm)) { ?>
    <p><?php esc_html_e('Please enter the form below. Sorry for the inconvenient.', 'rundiz-downloads'); ?></p>
    <form id="rundiz-downloads-antibot-form" method="post">
        <fieldset class="border rounded p-2">
            <?php if (isset($downloadRow)) { ?> 
            <div class="mb-2 row">
                <label for="download_name" class="col-sm-3 col-form-label"><?php esc_html_e('Downloads name', 'rundiz-downloads'); ?></label>
                <div class="col-sm-9">
                    <input id="download_name" class="form-control-plaintext" type="text" readonly value="<?php esc_html_e($downloadRow->download_name); ?>">
                </div>
            </div>
            <div class="mb-2 row">
                <label for="download_size" class="col-sm-3 col-form-label"><?php esc_html_e('File size', 'rundiz-downloads'); ?></label>
                <div class="col-sm-9">
                    <input id="download_size" class="form-control-plaintext" type="text" readonly value="<?php esc_attr_e(str_replace('.00', '', size_format($downloadRow->download_size, 2))); ?>">
                </div>
            </div>
            <div class="mb-2 row">
                <label for="download_file_name" class="col-sm-3 col-form-label"><?php esc_html_e('File name', 'rundiz-downloads'); ?></label>
                <div class="col-sm-9">
                    <input id="download_file_name" class="form-control-plaintext" type="text" readonly value="<?php esc_attr_e($downloadRow->download_file_name); ?>">
                </div>
            </div>
            <?php
            } else {
                trigger_error('$downloadRow is not defined.');
            }
            ?> 
        </fieldset>
        <div class="rundiz-downloads-d-none d-none" aria-hidden="true">
            <label for="<?php esc_attr_e($honeypotName); ?>"><?php esc_html_e('Please skip this field.', 'rundiz-downloads'); ?></label>
            <input id="<?php esc_attr_e($honeypotName); ?>" type="text" name="<?php esc_attr_e($honeypotName); ?>">
        </div>
        <div class="mb-3 form-check" aria-hidden="false">
            <input id="iamhuman" class="form-check-input" type="checkbox" name="iamhuman" value="1">
            <label class="form-check-label" for="iamhuman"><?php esc_html_e('I\'m human.', 'rundiz-downloads'); ?></label>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fontawesome-icon icon-download fas fa-download"></i> <?php esc_html_e('Continue to download', 'rundiz-downloads'); ?></button>
    </form><!--#rundiz-downloads-antibot-form-->
    <?php } ?>
</div><!--#main-column-->

<!-- You can copy this template file from this plugin folder's templates/RdDownloadsPage and put it in your theme to design it yours. For more information please read the readme file in certain folder -->
<?php
get_footer();
?>
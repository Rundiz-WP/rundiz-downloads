<?php
/**
 * This template is based on "Bootstrap Basic 4" theme.
 * 
 * This page require Bootstrap 4.0, Font Awesome 5.3.0 CSS.
 * 
 * Available variables please look in `App\Controllers\Front\RdDownloadsPage` class `subUseCaptcha()` method.
 */


get_header();

?>
<div id="main-column" class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
    <h1><?php _e('Human verification', 'rd-downloads'); ?></h1>
    <p><?php _e('Please enter the text you see on the image into the form below. Sorry for the inconvenient.', 'rd-downloads'); ?></p>

    <?php if (isset($form_result) && isset($form_result_msg)) { ?>
    <div class="alert alert-<?php echo ($form_result == 'success' ? 'success' : 'danger'); ?> alert-dismissible fade show" role="alert">
        <?php echo $form_result_msg; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
    </div>
    <?php } ?>

    <?php if (!isset($disableCaptchaForm) || (isset($disableCaptchaForm) && $disableCaptchaForm === false)) { ?>
    <form id="rd-downloads-captcha-form" method="post">
        <div class="form-group text-center">
            <img id="rd-downloads-captcha" class="img-fluid" src="<?php if (isset($captchaImage)) {echo esc_url($captchaImage);} ?>" alt="<?php _e('Captcha image', 'rd-downloads'); ?>">
        </div>
        <?php if (isset($useCaptchaAudio) && $useCaptchaAudio === true) { ?>
        <div class="hide hidden">
            <audio id="rd-downloads-captcha-audio" preload="none">
                <source id="rd-downloads-captcha-audio-source-wav" src="<?php if (isset($captchaAudio)) {echo esc_url($captchaAudio);} ?>" type="audio/wav">
            </audio>
        </div>
        <?php } ?>
        <div class="form-group text-center">
            <button id="rd-downloads-captcha-reload" class="btn btn-light" type="button" data-target="#rd-downloads-captcha">
                <i class="fontawesome-icon icon-reload fas fa-sync"></i>
            </button>
            <?php if (isset($useCaptchaAudio) && $useCaptchaAudio === true) { ?>
            <button id="rd-downloads-captcha-audio-controls" class="btn btn-light" type="button" data-target="#rd-downloads-captcha-audio">
                <i class="fontawesome-icon icon-play-audio fas fa-volume-up"></i> 
                <span class="sronly sr-only screen-reader-only screen-reader-text"><?php _e('Play audio', 'rd-downloads'); ?></span>
            </button>
            <?php } ?>
        </div>
        <div class="form-group">
            <input type="text" class="form-control" id="rddownloads_captcha" name="rddownloads_captcha" placeholder="<?php _e('Text', 'rd-downloads'); ?>" autocomplete="off">
            <small id="rddownloads_captcha_help" class="form-text text-muted"><?php _e('Please enter the text you see above.', 'rd-downloads'); ?></small>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fontawesome-icon icon-download fas fa-download"></i> <?php _e('Continue to download', 'rd-downloads'); ?></button>
    </form><!--#rd-downloads-captcha-form-->
    <?php } ?>
</div><!--#main-column-->

<!-- You can copy this template file from <?php echo __FILE__; ?> and put it in your theme to design it yours. For more information please read the readme file at <?php echo plugin_dir_path(RDDOWNLOADS_FILE) . 'templates'; ?> -->
<?php
get_footer();
?>
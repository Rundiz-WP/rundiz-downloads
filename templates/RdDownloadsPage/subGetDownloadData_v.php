<?php
/**
 * This template is based on "Bootstrap Basic 4" theme.
 * 
 * This page require Bootstrap 4.0, Font Awesome 5.3.0 CSS.
 * 
 * Available variables please look in `App\Controllers\Front\RdDownloadsPage` class `subUseCaptcha()` method.
 * 
 * This page only visible if download was not found.
 */


get_header();

?>
<div id="main-column" class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
    <div class="alert alert-danger">
        <h1 class="alert-heading"><?php _e('Not found', 'rd-downloads'); ?></h1>
        <p><?php _e('The download you have requested could not be found, please verify your link again.', 'rd-downloads'); ?></p>
    </div>
</div><!--#main-column-->

<!-- You can copy this template file from <?php echo __FILE__; ?> and put it in your theme to design it yours. For more information please read the readme file at <?php echo plugin_dir_path(RDDOWNLOADS_FILE) . 'templates'; ?> -->
<?php
get_footer();
?>
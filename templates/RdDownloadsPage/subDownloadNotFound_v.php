<?php
/**
 * This template is based on "Bootstrap Basic 4" theme.
 * 
 * This page require Bootstrap 4.0, Font Awesome 5.3.0 CSS.
 * 
 * To make very sure that it is download not found page, check $download_not_found variable must be true.
 * 
 * @package rd-downloads
 */


get_header();

?>
<div id="main-column" class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
    <?php if (isset($download_not_found) && true === $download_not_found) { ?> 
    <div class="alert alert-danger">
        <h1 class="alert-heading"><?php esc_html_e('Not found', 'rd-downloads'); ?></h1>
        <p><?php esc_html_e('The download you have requested could not be found, please verify your link again.', 'rd-downloads'); ?></p>
    </div>
    <?php }// endif; ?> 
</div><!--#main-column-->

<!-- You can copy this template file from this plugin folder's templates/RdDownloadsPage and put it in your theme to design it yours. For more information please read the readme file in certain folder. -->
<?php
get_footer();
?>
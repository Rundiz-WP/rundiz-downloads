<?php
/**
 * This template is based on "Bootstrap Basic 4" theme.
 * 
 * This page require Bootstrap 4.0, Font Awesome 5.3.0 CSS.
 * 
 * Available variables please look in `App\Controllers\Front\RdDownloadsPage` class `subCheckBannedUA()` method.
 * 
 * This page only visible if user agent has been blocked.
 * 
 * @package rd-downloads
 */


get_header();

?>
<div id="main-column" class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
    <div class="alert alert-danger">
        <h1 class="alert-heading"><?php esc_html_e('Forbidden', 'rd-downloads'); ?></h1>
        <p><?php esc_html_e('Your user agent has been blocked by the administrator. Please contact an administrator for your help.', 'rd-downloads'); ?></p>
        <p><?php 
            printf(
                /* translators: %s: Current user agent. */
                esc_html__('Your user agent: %s.', 'rd-downloads'),
                (isset($currentUserAgent) ? esc_html($currentUserAgent) : '')
            );
            echo '<br>' . PHP_EOL;
            printf(
                /* translators: %s: Match banned user agent. */
                esc_html__('Match banned user agent: %s.', 'rd-downloads'),
                (isset($matchBannedUserAgent) ? esc_html($matchBannedUserAgent) : '')
            );
        ?></p>
        <!-- <?php if (isset($currentUserAgent)) {echo esc_html($currentUserAgent);} ?> <?php if (isset($matchBannedUserAgent)) {echo esc_html($matchBannedUserAgent);} ?> -->
    </div>
</div><!--#main-column-->

<!-- You can copy this template file from this plugin folder's templates/RdDownloadsPage and put it in your theme to design it yours. For more information please read the readme file in certain folder -->
<?php
get_footer();
?>
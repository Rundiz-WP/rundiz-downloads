<?php
/**
 * Edit downloads help tab 2.
 * 
 * @package rundiz-downloads
 */


if (!defined('ABSPATH')) {
    exit();
}
?>
<p><?php esc_html_e('The Force download option will work with the local file only.', 'rundiz-downloads'); ?></p>
<p><?php 
printf(
    /* translators: %s: Force download option. */
    esc_html__('If you want to use redirect, the %s option in plugin setting page must be unchecked.', 'rundiz-downloads'), 
    '<strong>' . esc_html__('Force download', 'rundiz-downloads') . '</strong>'
); 
?></p>
<p><?php 
printf(
    /* translators: %s: Force download option. */
    esc_html__('If you choose this option to default, it will use %s option from plugin setting page.', 'rundiz-downloads'), 
    '<strong>' . esc_html__('Force download', 'rundiz-downloads') . '</strong>'
); 
?></p>
<p><?php
if (isset($rundiz_downloads_options['rdd_force_download']) && strval($rundiz_downloads_options['rdd_force_download']) === '1') {
    $rundiz_downloads_pluginSettingsUse = __('Force download', 'rundiz-downloads');
} else {
    $rundiz_downloads_pluginSettingsUse = __('redirect to file', 'rundiz-downloads');
}
printf(
    /* translators: %s: Force download option value for main plugin setting. */
    esc_html__('The plugin setting is using %s.', 'rundiz-downloads'), 
    '<strong>' . esc_html($rundiz_downloads_pluginSettingsUse) . '</strong>'
);
unset($rundiz_downloads_pluginSettingsUse);
?></p>
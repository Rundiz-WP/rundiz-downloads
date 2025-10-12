<p><?php esc_html_e('The Force download option will work with the local file only.', 'rd-downloads'); ?></p>
<p><?php 
printf(
    /* translators: %s: Force download option. */
    esc_html__('If you want to use redirect, the %s option in plugin setting page must be unchecked.', 'rd-downloads'), 
    '<strong>' . esc_html__('Force download', 'rd-downloads') . '</strong>'
); 
?></p>
<p><?php 
printf(
    /* translators: %s: Force download option. */
    esc_html__('If you choose this option to default, it will use %s option from plugin setting page.', 'rd-downloads'), 
    '<strong>' . esc_html__('Force download', 'rd-downloads') . '</strong>'
); 
?></p>
<p><?php
if (isset($rd_downloads_options['rdd_force_download']) && strval($rd_downloads_options['rdd_force_download']) === '1') {
    $pluginSettingsUse = __('Force download', 'rd-downloads');
} else {
    $pluginSettingsUse = __('redirect to file', 'rd-downloads');
}
printf(
    /* translators: %s: Force download option value for main plugin setting. */
    esc_html__('The plugin setting is using %s.', 'rd-downloads'), 
    '<strong>' . esc_html($pluginSettingsUse) . '</strong>'
);
unset($pluginSettingsUse);
?></p>
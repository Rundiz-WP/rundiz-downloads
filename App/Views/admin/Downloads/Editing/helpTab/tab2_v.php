<p><?php _e('The Force download option will work with the local file only.', 'rd-downloads'); ?></p>
<p><?php 
/* translators: %s: Force download option. */
printf(__('If you want to use redirect, the %s option in plugin setting page must be unchecked.', 'rd-downloads'), '<strong>' . __('Force download', 'rd-downloads') . '</strong>'); 
?></p>
<p><?php 
/* translators: %s: Force download option. */
printf(__('If you choose this option to default, it will use %s option from plugin setting page.', 'rd-downloads'), '<strong>' . __('Force download', 'rd-downloads') . '</strong>'); 
?></p>
<p><?php
if (isset($rd_downloads_options['rdd_force_download']) && $rd_downloads_options['rdd_force_download'] == '1') {
    $pluginSettingsUse = __('Force download', 'rd-downloads');
} else {
    $pluginSettingsUse = __('redirect to file', 'rd-downloads');
}
/* translators: %s: Force download option value for main plugin setting. */
echo sprintf(__('The plugin setting is using %s.', 'rd-downloads'), '<strong>' . $pluginSettingsUse . '</strong>');
unset($pluginSettingsUse);
?></p>
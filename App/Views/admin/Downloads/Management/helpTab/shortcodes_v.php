<?php
/**
 * Management page help tab short codes.
 * 
 * @package rundiz-downloads
 */


if (!defined('ABSPATH')) {
    exit();
}
?>
<p>
    <?php
    printf(
        /* translators: %1$s: Example basic shortcode, %2$s: text x in shortcode example, %3$s: download_id text, %4$s: id attribute in shortcode. */
        esc_html__('To display download button, use this shortcode %1$s, the %2$s value have to replace with the %3$s. The attribute %4$s is always required.', 'rundiz-downloads'),
        '<code>[rddownloads id="x"]</code>',
        '<code>x</code>',
        '<code>download_id</code>',
        '<code>id</code>'
    );
    ?>
</p>
<h4><?php esc_html_e('Additional attribute for shortcode', 'rundiz-downloads'); ?></h4>
<ul>
    <?php
    $rundiz_downloads_ShortcodeRdDownloads = new \RundizDownloads\App\Libraries\ShortcodeRdDownloads();
    $rundiz_downloads_attributes = $rundiz_downloads_ShortcodeRdDownloads->availableAttributes();
    foreach ($rundiz_downloads_attributes as $rundiz_downloads_attribute => $rundiz_downloads_item) {
        if (strtolower($rundiz_downloads_attribute) !== 'id') {
            echo '<li><code>' . esc_html($rundiz_downloads_attribute) . '</code>';
            if (isset($rundiz_downloads_item['helpmsg'])) {
                echo ' =&gt; ' . esc_html($rundiz_downloads_item['helpmsg']);
            }
            echo '</li>' . PHP_EOL;
        }
    }
    unset($rundiz_downloads_attribute, $rundiz_downloads_attributes, $rundiz_downloads_item);
    unset($rundiz_downloads_ShortcodeRdDownloads);
    ?>
</ul>
<p>
    <?php esc_html_e('You can use any of the attributes above combine together to match condition in the template and display its value.', 'rundiz-downloads'); ?><br>
    <?php esc_html_e('Example:', 'rundiz-downloads'); ?>
    <code>[rddownloads id="1" display_size="true"]</code>,
    <code>[rddownloads id="1" display_size="true" display_file_name="true"]</code>,
    <code>[rddownloads id="1" display_last_update="true" datetime_format="j/F/Y g:i A"]</code>
</p>
<p>
    <?php esc_html_e('These attributes will be working if the downloads element was set properly by the administrator.', 'rundiz-downloads'); ?>
</p>
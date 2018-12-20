<p>
    <?php
    printf(
        /* translators: %1$s: Example basic shortcode, %2$s: text x in shortcode example, %3$s: download_id text, %4$s: id attribute in shortcode. */
        __('To display download button, use this shortcode %1$s, the %2$s value have to replace with the %3$s. The attribute %4$s is always required.', 'rd-downloads'),
        '<code>[rddownloads id="x"]</code>',
        '<code>x</code>',
        '<code>download_id</code>',
        '<code>id</code>'
    );
    ?>
</p>
<h4><?php _e('Additional attribute for shortcode', 'rd-downloads'); ?></h4>
<ul>
    <?php
    $ShortcodeRdDownloads = new \RdDownloads\App\Libraries\ShortcodeRdDownloads();
    $attributes = $ShortcodeRdDownloads->availableAttributes();
    $availableShortcodeAttributes = '';
    foreach ($attributes as $attribute => $item) {
        if (strtolower($attribute) !== 'id') {
            echo '<li><code>' . $attribute . '</code>';
            if (isset($item['helpmsg'])) {
                echo ' =&gt; ' . $item['helpmsg'];
            }
            echo '</li>' . PHP_EOL;
        }
    }
    unset($attribute, $attributes, $item);
    unset($ShortcodeRdDownloads);
    ?>
</ul>
<p>
    <?php _e('You can use any of the attributes above combine together to match condition in the template and display its value.', 'rd-downloads'); ?><br>
    <?php _e('Example:', 'rd-downloads'); ?>
    <code>[rddownloads id="1" display_size="true"]</code>,
    <code>[rddownloads id="1" display_size="true" display_file_name="true"]</code>,
    <code>[rddownloads id="1" display_last_update="true" datetime_format="j/F/Y g:i A"]</code>
</p>
<p>
    <?php _e('These attributes will be working if the downloads element was set properly by the administrator.', 'rd-downloads'); ?>
</p>
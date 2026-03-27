<?php
/**
 * Edit downloads help tab 1.
 * 
 * @package rundiz-downloads
 */


if (!defined('ABSPATH')) {
    exit();
}
?>
<p><?php esc_html_e('To save the downloads data, the Download URL field is required.', 'rundiz-downloads'); ?></p>
<h3><?php esc_html_e('Local file', 'rundiz-downloads'); ?></h3>
<p><?php
printf(
    /* translators: %s: Full path to this plugin upload folder. */
    esc_html__('You can upload a file to the server using upload button below. You can also use FTP for large file size by uploading it to %s and then use file browser below to select the file.', 'rundiz-downloads'), 
    '<strong>' . esc_html(realpath($basedir . '/' . RundizDownloads\App\Libraries\FileSystem::UPLOAD_FOLDER_NAME)) . '</strong>'
)
?></p>
<p><?php esc_html_e('If you will be uploading the files using FTP, the file name should be web safe. For example: English, number, dash(-), underscore (_), no anything else.', 'rundiz-downloads'); ?></p>
<h3><?php esc_html_e('GitHub file', 'rundiz-downloads'); ?></h3>
<p>
    <?php esc_html_e('You can enter the download URL in the Download URL field directly by using one of these URL formats.', 'rundiz-downloads'); ?><br>
    <!-- These are technical data, I don't think they need to use translate function. -->
    https://github.com/&lt;owner&gt;/&lt;name&gt;<br>
    https://github.com/&lt;owner&gt;/&lt;name&gt;/archive/&lt;branch&gt;.zip<br>
    https://github.com/&lt;owner&gt;/&lt;name&gt;/archive/&lt;release&gt;.zip<br>
    https://github.com/&lt;owner&gt;/&lt;name&gt;/archive/&lt;release&gt;.tar.gz<br>
</p>
<p><?php
esc_html_e('Users cannot use auto correct GitHub URL if they don\'t connect to GitHub OAuth.', 'rundiz-downloads');
?></p>
<h3><?php esc_html_e('Any remote file', 'rundiz-downloads'); ?></h3>
<p><?php esc_html_e('You can enter the download URL in the Download URL field directly for any remotely hosted file.', 'rundiz-downloads'); ?></p>
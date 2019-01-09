<p><?php _e('To save the downloads data, the Download URL field is required.', 'rd-downloads'); ?></p>
<h3><?php _e('Local file', 'rd-downloads'); ?></h3>
<p><?php
/* translators: %s: Full path to this plugin upload folder. */
echo sprintf(__('You can upload a file to the server using upload button below. You can also use FTP for large file size by uploading it to %s and then use file browser below to select the file.', 'rd-downloads'), realpath($basedir . '/rd-downloads'))
?></p>
<p><?php _e('If you will be uploading the files using FTP, the file name should be web safe. For example: English, number, dash(-), underscore (_), no anything else.', 'rd-downloads'); ?></p>
<h3><?php _e('GitHub file', 'rd-downloads'); ?></h3>
<p>
    <?php _e('You can enter the download URL in the Download URL field directly by using one of these URL formats.', 'rd-downloads'); ?><br>
    <!-- These are technical data, I don't think they need to use translate function. -->
    https://github.com/&lt;owner&gt;/&lt;name&gt;<br>
    https://github.com/&lt;owner&gt;/&lt;name&gt;/archive/&lt;branch&gt;.zip<br>
    https://github.com/&lt;owner&gt;/&lt;name&gt;/archive/&lt;release&gt;.zip<br>
    https://github.com/&lt;owner&gt;/&lt;name&gt;/archive/&lt;release&gt;.tar.gz<br>
</p>
<p><?php
_e('Users cannot use auto correct GitHub URL if they don\'t connect to GitHub OAuth.', 'rd-downloads');
?></p>
<h3><?php _e('Any remote file', 'rd-downloads'); ?></h3>
<p><?php _e('You can enter the download URL in the Download URL field directly for any remotely hosted file.', 'rd-downloads'); ?></p>
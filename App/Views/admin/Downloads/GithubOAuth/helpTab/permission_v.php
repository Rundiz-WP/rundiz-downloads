<p>
    <?php
    _e('Users who are able to access this page must have permission to manage downloads.', 'rd-downloads');
    ?><br>
    <?php
    /* translators: %s: upload_files capability. */
    printf(__('To manage downloads (including add or edit downloads), user require %s capability.', 'rd-downloads'), '<strong>upload_files</strong>');
    ?>
</p>
<p><?php
/* translators: %1$s: Open link tag, %2$s: Close link tag. */
printf(__('For more information about these capabilities, please continue reading on %1$sWordPress%2$s website.', 'rd-downloads'), '<a href="https://codex.wordpress.org/Roles_and_Capabilities" target="WPDoc">', '</a>');
?></p>
<p>
    <?php
    /* translators: %s: edit_posts capability. */
    printf(__('To view downloads, user require %s capability.', 'rd-downloads'), '<strong>edit_posts</strong>');
    ?><br>
    <?php
    /* translators: %s: upload_files capability. */
    printf(__('To manage downloads (including add or edit downloads), user require %s capability.', 'rd-downloads'), '<strong>upload_files</strong>');
    ?><br>
    <?php
    /* translators: %s: upload_files capability. */
    printf(__('To delete an uploaded file, user require %s capability.', 'rd-downloads'), '<strong>upload_files</strong>');
    ?>
</p>
<p><?php
/* translators: %s: edit_others_posts capability. */
printf(__('Users cannot edit other\'s download if they don\'t have %s capability.', 'rd-downloads'), '<strong>edit_others_posts</strong>');
?></p>
<p><?php
_e('Users cannot use bulk action to update GitHub if they don\'t connect to GitHub OAuth.', 'rd-downloads');
?></p>
<p><?php
/* translators: %1$s: Open link tag, %2$s: Close link tag. */
printf(__('For more information about these capabilities, please continue reading on %1$sWordPress%2$s website.', 'rd-downloads'), '<a href="https://codex.wordpress.org/Roles_and_Capabilities" target="WPDoc">', '</a>');
?></p>
<p>
    <?php
    esc_html_e('Users who are able to access this page must have permission to manage downloads.', 'rundiz-downloads');
    ?><br>
    <?php
    /* translators: %s: upload_files capability. */
    printf(esc_html__('To manage downloads (including add or edit downloads), user require %s capability.', 'rundiz-downloads'), '<strong>upload_files</strong>');
    ?>
</p>
<p><?php
/* translators: %1$s: Open link tag, %2$s: Close link tag. */
printf(esc_html__('For more information about these capabilities, please continue reading on %1$sWordPress%2$s website.', 'rundiz-downloads'), '<a href="https://codex.wordpress.org/Roles_and_Capabilities" target="WPDoc">', '</a>');
?></p>
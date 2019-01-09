<p>
    <?php
    printf(
        /* translators: %1$s: HTTPS, %2$s: SSL verification */
        __('To sync secret key with GitHub, by default if this website is using %1$s then the %2$s will be enable by default.', 'rd-downloads'),
        '<strong>HTTPS</strong>',
        '<strong>' . __('SSL verification', 'rd-downloads') . '</strong>'
    );
    ?><br>
    <?php
    printf(
        /* translators: %1$s: Filter name, %2$s: "1" value (string). */
        __('To disable it, you have to go to your repository setting on GitHub and change this setting or use %1$s filter and change value to %2$s (string) and then sync again.', 'rd-downloads'),
        '<code>rddownloads_githubapi_webhookinsecure</code>',
        '<strong>\'1\'</strong>'
    );
    ?>
</p>
<dl>
    <dt><?php esc_html_e('Why am I unable to clear the logs?', 'rd-downloads'); ?></dt>
    <dd><?php 
    printf(
        /* translators: %1$s: administrator, %2$s: delete_users */
        esc_html__('You have to be an %1$s or have %2$s capability to clear the logs.', 'rd-downloads'),
        '<strong>' . esc_html__('Administrator') . '</strong>',
        '<strong>delete_users</strong>'
    ); 
    ?></dd>
</dl>
<dl>
    <dt><?php _e('Why am I unable to clear the logs?', 'rd-downloads'); ?></dt>
    <dd><?php 
    printf(
        /* translators: %1$s: administrator, %2$s: delete_users */
        __('You have to be an %1$s or have %2$s capability to clear the logs.', 'rd-downloads'),
        '<strong>' . __('Administrator') . '</strong>',
        '<strong>delete_users</strong>'
    ); 
    ?></dd>
</dl>
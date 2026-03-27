<?php
/**
 * Logs page help tab permission.
 * 
 * @package rundiz-downloads
 */


if (!defined('ABSPATH')) {
    exit();
}
?>
<dl>
    <dt><?php esc_html_e('Why am I unable to clear the logs?', 'rundiz-downloads'); ?></dt>
    <dd><?php 
    printf(
        /* translators: %1$s: administrator, %2$s: delete_users */
        esc_html__('You have to be an %1$s or have %2$s capability to clear the logs.', 'rundiz-downloads'),
        '<strong>' . esc_html__('Administrator', 'rundiz-downloads') . '</strong>',
        '<strong>delete_users</strong>'
    ); 
    ?></dd>
</dl>
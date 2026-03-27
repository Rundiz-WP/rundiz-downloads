<?php
/**
 * Settings views file.
 * 
 * @package rundiz-downloads
 * 
 * phpcs:disable Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace
 */


if (!defined('ABSPATH')) {
    exit();
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Downloads Settings', 'rundiz-downloads'); ?></h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php echo esc_attr($form_result_class); ?> notice is-dismissible">
        <p>
            <strong><?php echo wp_kses_post($form_result_msg); ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'rundiz-downloads'); ?></span></button>
    </div>
    <?php } ?> 

    <form method="post">
        <?php 
        wp_nonce_field(); 
        if (isset($settings_page)) {
            if (!is_file(dirname(dirname(__DIR__)) . '/config/kses_data.php')) {
                // if not found custom kses data. use custom kses data to make sure it is up to date with modern HTML elements and attributes that will work.
                // if not found then it should shown the error message, without translation because If this happens to a user from an unknown language, assistance may not be possible.
                throw new \Exception(esc_html('The file ' . dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'kses_data.php could not be found.'));
            }
            echo wp_kses($settings_page, include dirname(dirname(__DIR__)) . '/config/kses_data.php');
        } 
        submit_button(); 
        ?> 
    </form>
</div><!--.wrap-->
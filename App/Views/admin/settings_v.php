<div class="wrap">
    <h1><?php esc_html_e('Downloads Settings', 'rd-downloads'); ?></h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php esc_attr_e($form_result_class); ?> notice is-dismissible">
        <p>
            <strong><?php echo $form_result_msg;// phpcs:ignore ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.'); ?></span></button>
    </div>
    <?php } ?> 

    <form method="post">
        <?php wp_nonce_field(); ?> 
        <?php if (isset($settings_page)) {echo $settings_page;}// phpcs:ignore ?> 
        <?php submit_button(); ?> 
    </form>
</div><!--.wrap-->
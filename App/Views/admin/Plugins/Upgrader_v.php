<?php
/**
 * Upgrader's class view file.
 * 
 * @package rundiz-downloads
 */


if (!defined('ABSPATH')) {
    exit();
}

?>
<div class="wrap">
    <h1><?php esc_html_e('Manual update', 'rundiz-downloads'); ?></h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php echo esc_attr($form_result_class); ?> notice is-dismissible">
        <p>
            <strong><?php echo wp_kses_post($form_result_msg); ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'rundiz-downloads'); ?></span></button>
    </div>
    <?php } ?> 
    <div class="form-result-placeholder"></div>

    <form method="post">
        <?php wp_nonce_field(); ?> 
        <p><?php printf(
            // translators: %d Number of total actions.
            esc_html__('There are total %d actions for this manual update, please continue step by step.', 'rundiz-downloads'), 
            count($manualUpdateClasses)
        ); ?></p>
        <p><?php printf(
            /* translators: %1$s The number of already run action, %2$d The number of total actions. */
            esc_html__('You are running %1$s of %2$d.', 'rundiz-downloads'), 
            '<span class="already-run-total-action">0</span>', 
            count($manualUpdateClasses)
        ); ?></p>
        <button class="button button-primary manual-update-action-button" type="button"><?php esc_html_e('Start', 'rundiz-downloads'); ?></button> <span class="manual-update-action-placeholder"></span>
    </form>
</div>

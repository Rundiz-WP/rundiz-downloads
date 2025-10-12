<div class="wrap">
    <h1><?php esc_html_e('Manual update', 'rd-downloads'); ?></h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php esc_attr_e($form_result_class); ?> notice is-dismissible">
        <p>
            <strong><?php echo $form_result_msg;// phpcs:ignore ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.'); ?></span></button>
    </div>
    <?php } ?> 
    <div class="form-result-placeholder"></div>

    <form method="post">
        <?php wp_nonce_field(); ?> 
        <p><?php 
        /* translators: %d: Number of total manual update actions. */
        printf(esc_html__('There are total %d actions for this manual update, please continue step by step.', 'rd-downloads'), count($manualUpdateClasses)); 
        ?></p>
        <p><?php 
        /* translators: %1$s: Number with span wrapped of already action, %2$d: Number of total manual update actions. */
        printf(esc_html__('You are running %1$s of %2$d.', 'rd-downloads'), '<span class="already-run-total-action">0</span>', count($manualUpdateClasses)); 
        ?></p>
        <button class="button button-primary manual-update-action-button" type="button"><?php esc_html_e('Start', 'rd-downloads'); ?></button> <span class="manual-update-action-placeholder"></span>
    </form>
</div>


<script>
    var manualUpdateClasses = <?php 
    if (isset($manualUpdateClasses)) {
        echo wp_json_encode($manualUpdateClasses);
    }
    ?>;
</script>
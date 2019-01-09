<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('GitHub OAuth', 'rd-downloads'); ?></h1>

    <?php if (!isset($disconnected) || (isset($disconnected) && $disconnected === false)) { ?>
    <form method="post">
        <?php wp_nonce_field('rddownloads_github_disconnect', 'rddownloads_github_disconnect'); ?>
        <p>
            <?php _e('Are you sure to disconnect?', 'rd-downloads'); ?><br>
            <?php _e('You can connect again at anytime.', 'rd-downloads'); ?>
        </p>
        <input type="hidden" name="are_you_sure" value="yes">
        <button class="button button-primary"><i class="fas fa-sign-out-alt"></i> <?php _e('Confirm disconnect', 'rd-downloads'); ?></button>
    </form>
    <?php } else { ?>
    <p><?php
    /* translators: %1$s: Open link, %2$s: Close link. */
    printf(__('You has beed disconnected from GitHub. %1$sGo back%2$s to connect page.', 'rd-downloads'), '<a href="' . esc_url($thisPageUrl) . '">', '</a>');
    ?></p>
    <?php }// endif; ?>
</div><!--.wrap-->
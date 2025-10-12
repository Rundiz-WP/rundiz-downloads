<div class="wrap rd-downloads-page-githuboauth rd-downloads-page-githuboauth-subpage-disconnect">
    <h1 class="wp-heading-inline"><?php esc_html_e('GitHub OAuth', 'rd-downloads'); ?></h1>

    <?php if (!isset($disconnected) || (isset($disconnected) && false === $disconnected)) { ?>
    <form method="post">
        <?php wp_nonce_field('rddownloads_github_disconnect', 'rddownloads_github_disconnect'); ?>
        <p>
            <?php esc_html_e('Are you sure to disconnect?', 'rd-downloads'); ?><br>
            <?php esc_html_e('You can connect again at anytime.', 'rd-downloads'); ?>
        </p>
        <input type="hidden" name="are_you_sure" value="yes">
        <button class="button button-primary"><i class="fas fa-sign-out-alt"></i> <?php esc_html_e('Confirm disconnect', 'rd-downloads'); ?></button>
    </form>
    <?php } else { ?>
    <p><?php
    /* translators: %1$s: Open link, %2$s: Close link. */
    printf(esc_html__('You has beed disconnected from GitHub. %1$sGo back%2$s to connect page.', 'rd-downloads'), '<a href="' . esc_url($thisPageUrl) . '">', '</a>');
    ?></p>
    <?php }// endif; ?>
</div><!--.wrap-->
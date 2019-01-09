<?php
/* @var $GitHubOAuthListTable \RdDownloads\App\Models\GitHubOAuthListTable */
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('GitHub OAuth', 'rd-downloads'); ?></h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?>
    <div class="<?php echo $form_result_class; ?> notice is-dismissible">
        <p>
            <strong><?php echo $form_result_msg; ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.'); ?></span></button>
    </div>
    <?php } ?>
    <div class="rd-downloads-form-result-placeholder"></div>

    <div class="rddownloads-row">
        <div class="col">
            <?php
            if (isset($githubOAuthLink)) {
                if (!isset($accessToken) || (isset($accessToken) && empty($accessToken))) {
                    echo '<p>';
                    _e('You are not connected with GitHub. To make auto update, auto correct repository URL works you have to connect to GitHub.', 'rd-downloads');
                    echo '</p>' . PHP_EOL;
                }
                echo '<p><a class="button" href="' . esc_url($githubOAuthLink) . '"><i class="fas fa-sign-in-alt"></i> ' . __('Connect with GitHub', 'rd-downloads') . '</a></p>' . PHP_EOL;
            }

            if (isset($responseBody->data->viewer->login)) {
                echo '<div>' . PHP_EOL;
                /* translators: %s: Link to GitHub user profile page and display GitHub logged in name. */
                printf(__('You had connected to GitHub as %s.', 'rd-downloads'), '<a href="https://github.com/' . $responseBody->data->viewer->login . '" target="github_userprofile">' . $responseBody->data->viewer->login . '</a>');
                echo '</div>' . PHP_EOL;
            }
            ?>
        </div>
        <div class="col">
            <?php
            if (isset($responseBody->data->viewer->login)) {
                echo '<div class="text-right-sm"><a class="button" href="' . esc_url(admin_url('admin.php?page=rd-downloads_github_connect&subpage=disconnect')) . '"><i class="fas fa-sign-out-alt"></i> ' . __('Disconnect from GitHub', 'rd-downloads') . '</a></div>' . PHP_EOL;
            }
            ?>
        </div>
    </div><!--.rddownloads-row-->
    <?php
    if (isset($githubSecret)) {
    ?>
    <div class="rddownloads-row">
        <div class="col">
            <?php _e('Your secret key to use with GitHub auto update', 'rd-downloads'); ?>:
            <input id="rddownloads_githubwebhook_secret" type="password" name="rddownloads_githubwebhook_secret" value="<?php echo esc_attr($githubSecret); ?>">
            <button id="rddownloads_showhide_secret" class="button" type="button"><?php _e('Show/Hide', 'rd-downloads'); ?></button>
            <button id="rddownloads_regenerate_secret" class="button" type="button"><i class="fas fa-random"></i> <?php _e('Re-generate secret', 'rd-downloads'); ?></button>
            <button id="rddownloads_forcesync_github_secret" class="button" type="button" title="<?php esc_attr_e('This will be force synchronize secret with your GitHub repositories.', 'rd-downloads'); ?>"><i class="fas fa-sync-alt"></i> <?php _e('Force sync secret on GitHub.', 'rd-downloads'); ?></button>
        </div>
    </div><!--.rddownloads-row-->
    <?php
    }
    ?>

    <?php
    if (isset($GitHubOAuthListTable) && is_object($GitHubOAuthListTable)) {
        $GitHubOAuthListTable->display();
    }

    if (isset($responseCached) && $responseCached === true && current_user_can('manage_options')) {
        echo '<p>' . __('The list above was cached. To retreive the latest list, please clear the cache.', 'rd-downloads') . '</p>' . PHP_EOL;
    }
    ?>
</div><!--.wrap-->
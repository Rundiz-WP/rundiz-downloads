<?php // phpcs:disable
/*<div class="wrap">
    <h1><?php _e('Example of how to access settings values in option db.', 'rundiz-downloads'); ?></h1>

    <ol>
        <li><?php printf(__('Call to <code>%s</code>', 'rundiz-downloads'), '$this->getOptions();'); ?></li>
        <li><?php printf(__('Access this variable as global <code>%s</code>. This variable will be change, up to config in AppTrait.', 'rundiz-downloads'), 'global $rundiz_downloads_options;'); ?></li>
        <li><?php _e('Now, you can use this variable to access its array key anywhere.', 'rundiz-downloads'); ?></li>
    </ol>
    <h3>Example: <code>print_r($rundiz_downloads_options);</code></h3>
    <pre style="background-color: #333; border: 1px solid #ccc; color: #ddd; height: 500px; overflow: auto; padding: 10px;"><?php 
        if (isset($rundiz_downloads_options)) {
            echo htmlspecialchars(print_r($rundiz_downloads_options, true), ENT_QUOTES, get_option('blog_charset')); 
        }
    ?></pre>
</div>*/
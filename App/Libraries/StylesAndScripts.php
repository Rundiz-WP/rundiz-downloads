<?php
/**
 * Common styles (CSS) and scripts (JS).
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Libraries;


if (!defined('ABSPATH')) {
    exit();
}


if (!class_exists('\\RundizDownloads\\App\\Libraries\\StylesAndScripts')) {
    /**
     * Common use styles (CSS) and scripts (JS) class.
     */
    class StylesAndScripts
    {


        /**
         * Manually register hooks.
         */
        public function manualRegisterHooks()
        {
            // register stylesheets and scripts
            add_action('init', [$this, 'registerStylesAndScripts']);
            add_action('init', [$this, 'registerAdminStylesAndScripts']);
        }// manualRegisterHooks


        /**
         * Register admin styles and scripts.
         * 
         * Use for register only, do not enqueue here.
         *
         * The asset handle name will be added more specific name related to this plugin only to prevent the situation as the example below.  
         * Example:  
         * User has plugin AAA installed with this plugin.  
         * The plugin AAA and this plugin use the same CSS (or JS) with same handle name but the plugin AAA's asset version is older.  
         * The plugin AAA doesn't has CSS class that this plugin have such as `.sattellite-dish-icon`.  
         * This plugin is using `.sattellite-dish-icon` class but this plugin was loaded after plugin AAA, that means the CSS asset from this plugin will not be loaded.  
         * The asset class that this plugin is using will never work.  
         * To prevent this situation, the asset handle name **must** be more specific to the plugin.
         *
         * @link https://developer.wordpress.org/reference/functions/wp_register_style/ Function reference.
         * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/functions.wp-scripts.php The register style function `wp_add_inline_script` make a called to `_wp_scripts_maybe_doing_it_wrong()`.  
         *              The maybe doing it wrong function check that if `init` hook did called then it's work.
         */
        public function registerAdminStylesAndScripts()
        {
            if (is_admin()) {
                // common admin scripts
                wp_register_script('rundiz-downloads-handle-admin-common-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/Admin/common.js', [], RUNDIZDOWNLOADS_VERSION, true);

                // rundiz settings tabs
                wp_register_style('rundiz-downloads-settings-tabs-css', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/css/Admin/rd-settings-tabs.css', [], RUNDIZDOWNLOADS_VERSION);
                wp_register_script('rundiz-downloads-settings-tabs-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/Admin/rd-settings-tabs.js', [], RUNDIZDOWNLOADS_VERSION, true);

                // manual update
                wp_register_script(
                    'rundiz-downloads-settings-manual-update-js', 
                    plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/Admin/rd-settings-manual-update.js', 
                    ['rundiz-downloads-handle-admin-common-js'], 
                    RUNDIZDOWNLOADS_VERSION, 
                    true
                );

                // you can remove some or all of the line below if you don't use it. ---------
                // ace editor (code editor)
                wp_register_style('rundiz-downloads-settings-ace-editor-css', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/css/Admin/rd-settings-ace-editor.css', [], RUNDIZDOWNLOADS_VERSION);
                wp_register_script('rundiz-downloads-ace-editor-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/vendor/ace/ace.js', [], '1.2.3-minnoconflict', false);
                wp_register_script('rundiz-downloads-settings-ace-editor-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/Admin/rd-settings-ace-editor.js', ['rundiz-downloads-ace-editor-js'], RUNDIZDOWNLOADS_VERSION, true);
                // end removeable scripts/styles. ------------------------------------------------

                // common use js.
                wp_register_script('rundiz-downloads-common-functions-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/common-functions.js', [], RUNDIZDOWNLOADS_VERSION, true);
            }// endif;
        }// registerAdminStylesAndScripts


        /**
         * Register front-end & admin stylesheets and scripts for common use later.
         * 
         * Use for register only, do not enqueue here.
         *
         * Use more specific asset handle name. To see more description please read on `registerAdminStylesAndScripts()` method.
         *
         * @see \RundizDownloads\App\Libraries\StylesAndScripts::registerAdminStylesAndScripts() For more details.
         */
        public function registerStylesAndScripts()
        {
            // font awesome. choose css fonts instead of svg, see more at https://fontawesome.com/how-to-use/on-the-web/other-topics/performance
            // to name font awesome handle as `plugin-name-prefix-font-awesome5` is to prevent conflict with other plugins that maybe use older version but same handle that cause some newer icons in this plugin disappears.
            wp_register_style('rundiz-downloads-font-awesome5', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/vendor/fontawesome/css/all.min.css', [], '5.6.1');
        }// registerStylesAndScripts


    }// StylesAndScripts
}

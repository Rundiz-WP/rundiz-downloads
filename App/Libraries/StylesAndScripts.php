<?php
/**
 * Common styles (css) and scripts (js).
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Libraries;

if (!class_exists('\\RundizDownloads\\App\\Libraries\\StylesAndScripts')) {
    class StylesAndScripts
    {


        /**
         * Register admin styles and scripts.
         */
        public function registerAdminStylesAndScripts()
        {
            // rundiz settings tabs
            wp_register_style('rundiz-downloads-settings-tabs-css', plugin_dir_url(RUNDIZDOWNLOADS_FILE).'assets/css/rd-settings-tabs.css', [], RUNDIZDOWNLOADS_VERSION);
            wp_register_script('rundiz-downloads-settings-tabs-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE).'assets/js/rd-settings-tabs.js', ['jquery'], RUNDIZDOWNLOADS_VERSION, true);

            // manual update
            wp_register_script('rundiz-downloads-settings-manual-update-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/rd-settings-manual-update.js', ['jquery'], RUNDIZDOWNLOADS_VERSION, true);

            // you can remove some or all of the line below if you don't use it. ---------
            // ace editor (code editor)
            wp_register_style('rundiz-downloads-settings-ace-editor-css', plugin_dir_url(RUNDIZDOWNLOADS_FILE).'assets/css/rd-settings-ace-editor.css', [], RUNDIZDOWNLOADS_VERSION);
            wp_register_script('rundiz-downloads-ace-editor-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE).'assets/js/ace/ace.js', [], '1.2.3-minnoconflict', false);
            wp_register_script('rundiz-downloads-settings-ace-editor-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE).'assets/js/rd-settings-ace-editor.js', ['rundiz-downloads-ace-editor-js'], RUNDIZDOWNLOADS_VERSION, true);
            // end removeable scripts/styles. ------------------------------------------------

            // common use js.
            wp_register_script('rundiz-downloads-common-functions-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/common-functions.js', [], RUNDIZDOWNLOADS_VERSION, true);
        }// registerAdminStylesAndScripts


        /**
         * Register front-end & admin stylesheets and scripts for common use later.
         */
        public function registerStylesAndScripts()
        {
            // font awesome. choose css fonts instead of svg, see more at https://fontawesome.com/how-to-use/on-the-web/other-topics/performance
            // to name font awesome handle as `plugin-name-prefix-font-awesome5` is to prevent conflict with other plugins that maybe use older version but same handle that cause some newer icons in this plugin disappears.
            wp_register_style('rundiz-downloads-font-awesome5', plugin_dir_url(RUNDIZDOWNLOADS_FILE).'assets/fontawesome/css/all.min.css', [], '5.6.1');
        }// registerStylesAndScripts


        /**
         * Manually register hooks.
         */
        public function manualRegisterHooks()
        {
            // register stylesheets and scripts
            add_action('admin_enqueue_scripts', [$this, 'registerStylesAndScripts']);
            add_action('admin_enqueue_scripts', [$this, 'registerAdminStylesAndScripts']);
            add_action('wp_enqueue_scripts', [$this, 'registerStylesAndScripts']);
        }// manualRegisterHooks


    }
}
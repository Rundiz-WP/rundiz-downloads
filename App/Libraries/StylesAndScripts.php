<?php
/**
 * Common styles (css) and scripts (js).
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\StylesAndScripts')) {
    class StylesAndScripts
    {


        /**
         * Register admin styles and scripts.
         */
        public function registerAdminStylesAndScripts()
        {
            // rundiz settings tabs
            wp_register_style('rd-downloads-settings-tabs-css', plugin_dir_url(RDDOWNLOADS_FILE).'assets/css/rd-settings-tabs.css', [], RDDOWNLOADS_VERSION);
            wp_register_script('rd-downloads-settings-tabs-js', plugin_dir_url(RDDOWNLOADS_FILE).'assets/js/rd-settings-tabs.js', ['jquery'], RDDOWNLOADS_VERSION, true);

            // manual update
            wp_register_script('rd-downloads-settings-manual-update', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/rd-settings-manual-update.js', ['jquery'], RDDOWNLOADS_VERSION, true);

            // you can remove some or all of the line below if you don't use it. ---------
            // ace editor (code editor)
            wp_register_style('rd-downloads-settings-ace-editor-css', plugin_dir_url(RDDOWNLOADS_FILE).'assets/css/rd-settings-ace-editor.css', [], RDDOWNLOADS_VERSION);
            wp_register_script('rd-downloads-ace-editor-js', plugin_dir_url(RDDOWNLOADS_FILE).'assets/js/ace/ace.js', ['jquery'], '1.2.3-minnoconflict', false);
            wp_register_script('rd-downloads-settings-ace-editor-js', plugin_dir_url(RDDOWNLOADS_FILE).'assets/js/rd-settings-ace-editor.js', ['rd-downloads-ace-editor-js'], RDDOWNLOADS_VERSION, true);
            // end removeable scripts/styles. ------------------------------------------------

            // common use js.
            wp_register_script('rd-downloads-common-functions', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/common-functions.js', ['jquery'], RDDOWNLOADS_VERSION, true);
        }// registerAdminStylesAndScripts


        /**
         * Register front-end & admin stylesheets and scripts for common use later.
         */
        public function registerStylesAndScripts()
        {
            // font awesome. choose css fonts instead of svg, see more at https://fontawesome.com/how-to-use/on-the-web/other-topics/performance
            // to name font awesome handle as `plugin-name-prefix-font-awesome5` is to prevent conflict with other plugins that maybe use older version but same handle that cause some newer icons in this plugin disappears.
            wp_register_style('rd-downloads-font-awesome5', plugin_dir_url(RDDOWNLOADS_FILE).'assets/fontawesome/css/all.min.css', [], '5.6.1');
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
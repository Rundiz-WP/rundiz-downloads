<?php
/**
 * Deactivate the plugin action.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Plugins;

if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Plugins\\Deactivation')) {
    class Deactivation implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Deactivate the plugin.
         */
        public function deactivate()
        {
            // do something that will be happens on deactivate plugin.
            // remove all added rewrite rules.
            flush_rewrite_rules();
        }// deactivate


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // register deactivate hook
            register_deactivation_hook(RUNDIZDOWNLOADS_FILE, [$this, 'deactivate']);
        }// registerHooks


    }
}
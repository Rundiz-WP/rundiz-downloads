<?php
/**
 * Deactivate the plugin action.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Plugins;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Plugins\\Deactivation')) {
    class Deactivation implements \RdDownloads\App\Controllers\ControllerInterface
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
            register_deactivation_hook(RDDOWNLOADS_FILE, [$this, 'deactivate']);
        }// registerHooks


    }
}
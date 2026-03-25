<?php
/**
 * Enqueue CSS for front-end.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Front;

if (!class_exists('\\RundizDownloads\\App\\Controllers\\Front\\EnqueueCss')) {
    class EnqueueCss implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Enqueue styles.
         */
        public function enqueueStyles()
        {
            wp_enqueue_style('rundiz-downloads-font-awesome5');
            wp_enqueue_style('rundiz-downloads-front-css', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/css/front/rd-downloads.min.css', [], RUNDIZDOWNLOADS_VERSION);
        }// enqueueStyles


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
        }// registerHooks


    }
}
<?php
/**
 * Enqueue CSS for front-end.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Front\\EnqueueCss')) {
    class EnqueueCss implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Enqueue styles.
         */
        public function enqueueStyles()
        {
            wp_enqueue_style('rd-downloads-font-awesome5');
            wp_enqueue_style('rd-downloads-front-css', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/css/front/rd-downloads.min.css', [], RDDOWNLOADS_VERSION);
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
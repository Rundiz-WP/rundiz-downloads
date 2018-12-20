<?php
/**
 * [rddownloads] shortcode.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Shortcodes;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Shortcodes\\RdDownloads')) {
    class RdDownloads implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Convert shortcode.
         * 
         * @param array $atts
         */
        public function convertShortcode($atts)
        {
            $ShortcodeRdDownloads = new \RdDownloads\App\Libraries\ShortcodeRdDownloads();
            return $ShortcodeRdDownloads->renderHtml($atts);
        }// convertShortcode


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_shortcode('rddownloads', [$this, 'convertShortcode']);
        }// registerHooks


    }
}
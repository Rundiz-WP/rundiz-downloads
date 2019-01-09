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
            $cacheKey = 'rd-downloads.shortcode.rddownloads.blog-id-' . get_current_blog_id() . '_atts-' . md5(wp_json_encode($atts));
            $SimpleCache = new \RdDownloads\App\Libraries\Cache();
            $rendered = $SimpleCache->getInstance()->get($cacheKey);

            if ($rendered === false) {
                $ShortcodeRdDownloads = new \RdDownloads\App\Libraries\ShortcodeRdDownloads();
                $rendered = $ShortcodeRdDownloads->renderHtml($atts);
                $cacheLifetime = apply_filters('rddownloads_cachelifetime_shortcode', (6 * 60 * 60));// hours * minutes * seconds = total seconds.
                $SimpleCache->getInstance()->save($cacheKey, $rendered, $cacheLifetime);
                unset($cacheLifetime, $ShortcodeRdDownloads);
            }

            unset($cacheKey, $SimpleCache);
            return $rendered;
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
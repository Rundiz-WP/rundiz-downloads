<?php
/**
 * [rddownloads] shortcode.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Shortcodes;

if (!class_exists('\\RundizDownloads\\App\\Controllers\\Shortcodes\\RdDownloads')) {
    class RdDownloads implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Convert shortcode.
         *
         * @param array $atts
         */
        public function convertShortcode($atts)
        {
            $cacheKey = 'rundiz-downloads.shortcode.rddownloads.blog-id-' . get_current_blog_id() . '_atts-' . md5(wp_json_encode($atts));
            $SimpleCache = new \RundizDownloads\App\Libraries\Cache();
            $rendered = $SimpleCache->getInstance()->get($cacheKey);

            if (false === $rendered) {
                $ShortcodeRdDownloads = new \RundizDownloads\App\Libraries\ShortcodeRdDownloads();
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
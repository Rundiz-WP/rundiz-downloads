<?php
/**
 * Settings > Cache
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Settings\Xhr;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Settings\\Xhr\\XhrCache')) {
    /**
     * XhrCache class.
     */
    class XhrCache extends \RundizDownloads\App\Controllers\XhrBased implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Clear cache.
         */
        public function clearCache()
        {
            $this->commonAccessCheck(['post'], ['rundiz-downloads-settings_ajax-settings-nonce', 'security']);

            $SimpleCache = new \RundizDownloads\App\Libraries\Cache();
            $result = $SimpleCache->getInstance()->clear();
            unset($SimpleCache);

            wp_send_json(['result' => $result], 200);
        }// clearCache


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdDownloadsSettingsClearCache', [$this, 'clearCache']);
            }
        }// registerHooks


    }// XhrCache
}

<?php
/**
 * Settings > Cache
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Settings\Xhr;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Settings\\Xhr\\XhrCache')) {
    class XhrCache extends \RdDownloads\App\Controllers\XhrBased implements \RdDownloads\App\Controllers\ControllerInterface
    {


        public function clearCache()
        {
            $this->commonAccessCheck(['post'], ['rd-downloads-settings_ajax-settings-nonce', 'security']);

            $SimpleCache = new \RdDownloads\App\Libraries\Cache();
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


    }
}
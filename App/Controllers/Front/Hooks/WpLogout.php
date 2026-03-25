<?php
/**
 * Hook into logout page.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Front\Hooks;

if (!class_exists('\\RundizDownloads\\App\\Controllers\\Front\\Hooks\\WpLogout')) {
    class WpLogout implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Disconnect GitHub OAuth.
         */
        public function disconnectGitHubOAuth()
        {
            $Github = new \RundizDownloads\App\Libraries\Github();
            $Github->oauthDisconnect();
            unset($Github);
        }// disconnectGitHubOAuth


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('wp_logout', [$this, 'disconnectGitHubOAuth']);
        }// registerHooks


    }
}
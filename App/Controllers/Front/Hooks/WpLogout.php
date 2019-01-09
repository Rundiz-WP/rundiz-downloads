<?php
/**
 * Hook into logout page.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front\Hooks;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Front\\Hooks\\WpLogout')) {
    class WpLogout implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Disconnect GitHub OAuth.
         */
        public function disconnectGitHubOAuth()
        {
            $Github = new \RdDownloads\App\Libraries\Github();
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
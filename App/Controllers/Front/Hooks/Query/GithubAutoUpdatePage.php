<?php
/**
 * GitHub auto update.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front\Hooks\Query;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Front\\Hooks\\Query\\GithubAutoUpdatePage')) {
    class GithubAutoUpdatePage implements \RdDownloads\App\Controllers\ControllerInterface
    {


        use \RdDownloads\App\AppTrait;


        /**
         * Accept GitHub webhook for auto update.
         * 
         * This method contain `exit()` function, after call to this method where pagename is matched the process will be stopped.
         */
        public function githubAutoUpdatePage()
        {
            if (get_query_var('pagename') !== 'rddownloads_github_autoupdate') {
                return false;
            }

            $Github = new \RdDownloads\App\Libraries\Github();
            if ($Github->isSettingToAutoUpdate() !== true) {
                return false;
            }
            unset($Github);

            $GitHubAutoUpdatePage = new GithubAutoUpdatePage\GithubAutoUpdatePage();
            $GitHubAutoUpdatePage->pageIndex();
            unset($GitHubAutoUpdatePage);

            exit();// required to display just thispage, otherwise the normal WordPress page will be render.
        }// githubAutoUpdatePage


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (!is_admin()) {
                add_action('template_redirect', [$this, 'githubAutoUpdatePage']);
            }
        }// registerHooks


    }
}
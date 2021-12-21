<?php
/**
 * Download page from querystring.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front\Hooks\Query;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Front\\Hooks\\Query\\DownloadPage')) {
    class DownloadPage implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Select which page and sub-page will be use.
         * 
         * In this method will call to other controllers depend on sub page.<br>
         * This method will be send output such as echo, response including headers.
         */
        public function goToRdDownloadsPage()
        {
            if (get_query_var('pagename') == 'rddownloads_page') {
                $subpage = get_query_var('rddownloads_subpage');
                switch ($subpage) {
                    default:
                        $RdDownloadsPage = new DownloadPage\RdDownloadsPage();
                        $RdDownloadsPage->pageIndex(intval(get_query_var('download_id')));
                        unset($RdDownloadsPage);
                }// endswitch;
                unset($subpage);

                exit();// required to display just thispage, otherwise the normal WordPress page will be render.
            }
        }// goToRdDownloadsPage


        /**
         * Setup additional query variable.
         * 
         * @param array $vars
         * @return array
         */
        public function queryVars($vars)
        {
            $vars[] = 'rddownloads_subpage';
            $vars[] = 'download_id';

            return $vars;
        }// queryVars


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (!is_admin()) {
                add_filter('query_vars', [$this, 'queryVars']);
                add_action('template_redirect', [$this, 'goToRdDownloadsPage']);
            }
        }// registerHooks


    }
}
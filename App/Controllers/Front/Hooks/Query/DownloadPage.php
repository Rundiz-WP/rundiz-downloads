<?php
/**
 * Download page from querystring.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Front\Hooks\Query;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Front\\Hooks\\Query\\DownloadPage')) {
    /**
     * DownloadPage class.
     */
    class DownloadPage implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * @var string Download query string name. This constant must be public.
         */
        const DOWNLOAD_QUERY_VAR = 'rddownloads_page';


        /**
         * Select which page and sub-page will be use.
         * 
         * In this method will call to other controllers depend on sub page.<br>
         * This method will be send output such as echo, response including headers.
         */
        public function goToRdDownloadsPage()
        {
            if (get_query_var('pagename') === self::DOWNLOAD_QUERY_VAR) {
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
         * @param array $vars Query variables.
         * @return array
         */
        public function queryVars($vars)
        {
            $vars[] = 'rddownloads_subpage';
            $vars[] = 'download_id';
            $vars[] = 'rddownloads_redir_set_cookie';
            $vars[] = 'rddownloads_http_referrer';

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


    }// DownloadPage
}

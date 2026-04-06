<?php
/**
 * Download statistic dashboard widget.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Hooks\DashboardWidgets;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Hooks\\DashboardWidgets\\DownloadStat')) {
    /**
     * Download stat class.
     */
    class DownloadStat implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Add dashboard widget
         */
        public function addWidget()
        {
            wp_add_dashboard_widget(
                'rundiz_downloads_dashboard_widget_downloadstat',
                __('Download Statistics', 'rundiz-downloads'),
                [$this, 'displayDownloadStat']
            );

            wp_enqueue_style('rundiz-downloads-dashboard-widget-css', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/css/Admin/Hooks/DashboardWidgets/DownloadStat/dashboard-widget.css', ['rundiz-downloads-font-awesome5'], RUNDIZDOWNLOADS_VERSION);

            wp_enqueue_script('rundiz-downloads-chart-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/vendor/chartjs/Chart.bundle.min.js', [], '2.7.3', true);
            wp_enqueue_script('rundiz-downloads-dashboard-widget-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/Admin/Hooks/DashboardWidgets/DownloadStat/dashboard-widget.js', ['jquery', 'rundiz-downloads-chart-js', 'wp-util'], RUNDIZDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rundiz-downloads-dashboard-widget-js',
                'RdDownloads',
                [
                    'nonce' => wp_create_nonce('rundiz-downloads_dashboard-widget-nonce'),
                    'txtGettingData' => __('Getting data, please wait.', 'rundiz-downloads'),
                    'txtNoTopDownload' => __('There are no data.', 'rundiz-downloads'),
                    'txtTotalDownload' => __('Total downloads', 'rundiz-downloads'),
                    'txtTotalErrorDownload' => __('Total errors', 'rundiz-downloads'),
                    'txtTotalAntibotFailed' => __('Total failed on anti robot', 'rundiz-downloads'),
                ]
            );
        }// addWidget


        /**
         * Display download statistic.
         */
        public function displayDownloadStat()
        {
            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Hooks/DashboardWidgets/DownloadStat/displayDownloadStat_v');
            unset($Loader);
        }// displayDownloadStat


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('wp_dashboard_setup', [$this, 'addWidget']);
        }// registerHooks


    }// DownloadStat
}

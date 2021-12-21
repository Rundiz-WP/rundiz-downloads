<?php
/**
 * Download statistic dashboard widget.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Hooks\DashboardWidgets;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Hooks\\DashboardWidgets\\DownloadStat')) {
    class DownloadStat implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Add dashboard widget
         */
        public function addWidget()
        {
            wp_add_dashboard_widget(
                'rddownloads_dashboard_widget_downloadstat',
                __('Download Statistics', 'rd-downloads'),
                [$this, 'displayDownloadStat']
            );

            wp_enqueue_style('rd-downloads-dashboard-widget', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/css/admin/Hooks/DashboardWidgets/DownloadStat/dashboard-widget.css', ['rd-downloads-font-awesome5'], RDDOWNLOADS_VERSION);

            wp_enqueue_script('rd-downloads-chartjs', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/vendor/Chart.bundle.min.js', [], '2.7.3', true);
            wp_enqueue_script('rd-downloads-dashboard-widget', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/admin/Hooks/DashboardWidgets/DownloadStat/dashboard-widget.js', ['jquery', 'rd-downloads-chartjs', 'wp-util'], RDDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rd-downloads-dashboard-widget',
                'RdDownloads',
                [
                    'nonce' => wp_create_nonce('rd-downloads_dashboard-widget_nonce'),
                    'txtGettingData' => __('Getting data, please wait.', 'rd-downloads'),
                    'txtNoTopDownload' => __('There are no data.', 'rd-downloads'),
                    'txtTotalDownload' => __('Total downloads', 'rd-downloads'),
                    'txtTotalErrorDownload' => __('Total errors', 'rd-downloads'),
                    'txtTotalAntibotFailed' => __('Total failed on anti robot', 'rd-downloads'),
                ]
            );
        }// addWidget


        /**
         * Display download statistic.
         */
        public function displayDownloadStat()
        {
            $Loader = new \RdDownloads\App\Libraries\Loader();
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


    }
}
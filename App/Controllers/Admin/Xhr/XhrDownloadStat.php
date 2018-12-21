<?php
/**
 * XHR download stat for dashboard widget.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Xhr;


if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Xhr\\XhrDownloadStat')) {
    class XhrDownloadStat extends \RdDownloads\App\Controllers\XhrBased implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Get all downloads daily statistic.
         * 
         * @link https://stackoverflow.com/a/2041619/128761 Query last 30 days example.
         * @global \wpdb $wpdb
         */
        public function allDownloadsDailyStat()
        {
            $this->commonAccessCheck(['get'], ['rd-downloads_dashboard-widget_nonce', 'security']);

            global $wpdb;

            $output = [];
            $output['part_date_gmt'] = [];

            $tableRdDownloads = '`' . $wpdb->prefix . 'rd_downloads`';
            $tableRdDownloadLogs = '`' . $wpdb->prefix . 'rd_download_logs`';

            // setup date for graph axis. this can be use in sql statement.
            $output['part_date_gmt'][] = current_time('Y-m-d', true);
            $Date = new \DateTime($output['part_date_gmt'][0]);
            for ($i = 1; $i <= 30; $i++) {
                $Date->modify('-1 day');
                $output['part_date_gmt'][] = $Date->format('Y-m-d');
            }// endfor;
            unset($Date, $i);
            sort($output['part_date_gmt'], SORT_NATURAL);

            // get total success downloads ---------------------------------------------------------
            $sql = 'SELECT ' . $tableRdDownloadLogs . '.*, 
                COUNT(IF (`dl_status` = \'user_dl_success\', 1, NULL)) AS `dl_total_success`,
                COUNT(IF (`dl_status` = \'user_dl_error\', 1, NULL)) AS `dl_total_error`
                FROM ' . $tableRdDownloadLogs . '
                WHERE 
                    (
                        `dl_status` = %s
                        OR `dl_status` = %s
                    )
                    AND (
                        DATE(`dl_date_gmt`) >= %s
                        AND DATE(`dl_date_gmt`) <= %s
                    )
                GROUP BY DAY(`dl_date_gmt`)
                ORDER BY `dl_date_gmt` ASC';
            $data = [];
            $data[] = 'user_dl_success';
            $data[] = 'user_dl_error';
            $data[] = min($output['part_date_gmt']);
            $data[] = max($output['part_date_gmt']);
            $prepared = $wpdb->prepare($sql, $data);
            $results = $wpdb->get_results($prepared);

            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                $output['debug_sql'] = $sql;
                //$output['debug_prepared'] = var_export($prepared, true);
                $output['debug_last_error'] = var_export($wpdb->last_error, true);
                $output['debug_last_query'] = var_export($wpdb->last_query, true);
            }
            unset($data, $prepared, $sql);

            // prepare values for generated dates. the value will be 0 by default.
            foreach ($output['part_date_gmt'] as $key => $date) {
                $output['part_total_success'][$key] = 0;
                $output['part_total_error'][$key] = 0;
            }// endforeach;
            unset($data, $key);

            $output['total'] = count($results);
            $output['results'] = $results;

            if (is_array($results) || is_object($results)) {
                foreach ($results as $row) {
                    $dateOnly = mysql2date('Y-m-d', $row->dl_date_gmt);
                    $dateKey = array_search($dateOnly, $output['part_date_gmt']);

                    if ($dateKey !== false) {
                        if (isset($row->dl_total_success) && isset($output['part_total_success'][$dateKey])) {
                            $output['part_total_success'][$dateKey] = (isset($row->dl_total_success) ? $row->dl_total_success : 0);
                        }
                        if (isset($row->dl_total_error) && isset($output['part_total_error'][$dateKey])) {
                            $output['part_total_error'][$dateKey] = (isset($row->dl_total_error) ? $row->dl_total_error : 0);
                        }
                    }

                    unset($dateKey, $dateOnly);
                }
                unset($row);
            }
            unset($results);

            unset($tableRdDownloadLogs, $tableRdDownloads);

            wp_send_json($output, 200);
        }// allDownloadsDailyStat


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdDownloadsDashboardWidgetAllDownloadsDailyStat', [$this, 'allDownloadsDailyStat']);
                add_action('wp_ajax_RdDownloadsDashboardWidgetTopDownloads', [$this, 'topDownloads']);
            }
        }// registerHooks


        /**
         * Get top downloads.
         * 
         * @todo [rd-downloads] create query for top downloads.
         * @global \wpdb $wpdb
         */
        public function topDownloads()
        {
            $this->commonAccessCheck(['get'], ['rd-downloads_dashboard-widget_nonce', 'security']);

            sleep(2);
            wp_send_json(['test' => 'okay', 'get' => $_GET], 200);
        }// topDownloads


    }
}
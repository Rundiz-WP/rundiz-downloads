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
         * Get all downloads daily statistic for display in graph on admin dashboard.
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

            $cacheKey = 'rd-downloads.dashboard-widget.all-downloads-daily-stat.blog-id-' . get_current_blog_id();
            $SimpleCache = new \RdDownloads\App\Libraries\Cache();
            $results = $SimpleCache->getInstance()->get($cacheKey);

            if ($results === false) {
                // get total success and error downloads ---------------------------------------------------------
                $sql = 'SELECT ' . $tableRdDownloadLogs . '.*,
                    COUNT(IF (`dl_status` = \'user_dl_success\', 1, NULL)) AS `dl_total_success`,
                    COUNT(IF (`dl_status` = \'user_dl_error\', 1, NULL)) AS `dl_total_error`,
                    COUNT(IF (`dl_status` = \'user_dl_antbotfailed\', 1, NULL)) AS `dl_total_antibot_failed`
                    FROM ' . $tableRdDownloadLogs . '
                    WHERE
                        (
                            `dl_status` = %s
                            OR `dl_status` = %s
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
                $data[] = 'user_dl_antbotfailed';
                $data[] = min($output['part_date_gmt']);
                $data[] = max($output['part_date_gmt']);
                $prepared = $wpdb->prepare($sql, $data);
                $results = $wpdb->get_results($prepared);

                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    $output['debug_sql'] = $sql;
                    $output['debug_last_error'] = var_export($wpdb->last_error, true);
                    $output['debug_last_query'] = var_export($wpdb->last_query, true);
                }
                unset($data, $prepared, $sql);

                $cacheLifetime = apply_filters('rddownloads_cachelifetime_dashboardwidget_alldownloadsdailystat', (3 * 60 * 60));// hours * minutes * seconds = total seconds.
                $SimpleCache->getInstance()->save($cacheKey, $results, $cacheLifetime);
                unset($cacheLifetime);
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    $output['cached_results'] = true;
                }
            }// endif; $result is not cached.

            // prepare values for generated dates. the value will be 0 by default.
            foreach ($output['part_date_gmt'] as $key => $date) {
                $output['part_total_success'][$key] = 0;
                $output['part_total_error'][$key] = 0;
                $output['part_total_antibotfailed'][$key] = 0;
            }// endforeach;
            unset($data, $key);

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
                        if (isset($row->dl_total_antibot_failed) && isset($output['part_total_antibotfailed'][$dateKey])) {
                            $output['part_total_antibotfailed'][$dateKey] = (isset($row->dl_total_antibot_failed) ? $row->dl_total_antibot_failed : 0);
                        }
                    }

                    unset($dateKey, $dateOnly);
                }
                unset($row);
            }

            unset($tableRdDownloadLogs, $tableRdDownloads);

            $output['total'] = (is_array($results) || is_object($results) ? count($results) : 0);
            $output['results'] = $results;

            unset($cacheKey, $results, $SimpleCache);

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
         * Get top downloads for display as a list on admin dashboard.
         *
         * @link https://stackoverflow.com/a/2041619/128761 Query last xx days example.
         * @global \wpdb $wpdb
         */
        public function topDownloads()
        {
            $this->commonAccessCheck(['get'], ['rd-downloads_dashboard-widget_nonce', 'security']);

            global $wpdb;

            $output = [];
            $scope = filter_input(INPUT_GET, 'scope', FILTER_SANITIZE_NUMBER_INT);
            if ($scope != '0' && $scope != '1' && $scope != '7' && $scope != '30') {
                $scope = '0';
            }

            $cacheKey = 'rd-downloads.dashboard-widget.top-results-list.blog-id-' . get_current_blog_id() . '_scope-' . $scope;
            $SimpleCache = new \RdDownloads\App\Libraries\Cache();
            $results = $SimpleCache->getInstance()->get($cacheKey);

            if ($results === false) {
                $tableRdDownloads = '`' . $wpdb->prefix . 'rd_downloads`';
                $tableRdDownloadLogs = '`' . $wpdb->prefix . 'rd_download_logs`';

                if ($scope > '0') {
                    $sql = 'SELECT ' . $tableRdDownloadLogs . '.*';
                    $sql .= ', ' . $tableRdDownloads . '.*';
                    $sql .= ', COUNT(' . $tableRdDownloadLogs . '.`download_id`) AS `download_count`';
                    $sql .= ', DATE_FORMAT(`dl_date_gmt`, %s)';
                    $sql .= ' FROM ' . $tableRdDownloadLogs;
                    $sql .= ' LEFT JOIN ' . $tableRdDownloads . ' ON ' . $tableRdDownloads . '.`download_id` = ' . $tableRdDownloadLogs . '.`download_id`';
                    $sql .= ' WHERE `dl_status` = %s';
                    $sql .= ' AND `dl_date_gmt` BETWEEN NOW() - INTERVAL ' . $scope . ' DAY AND NOW()';
                    $sql .= ' GROUP BY ' . $tableRdDownloadLogs . '.`download_id`';
                    $sql .= ' ORDER BY `download_count` DESC';
                    $data = [];
                    $data[] = '%m/%d/%Y';
                    $data[] = 'user_dl_success';
                } else {
                    $sql = 'SELECT ' . $tableRdDownloads . '.* FROM ' . $tableRdDownloads;
                    $sql .= ' GROUP BY ' . $tableRdDownloads . '.`download_id`';
                    $sql .= ' ORDER BY `download_count` DESC';
                }
                $sql .= ' LIMIT 0, 5';

                if (isset($data) && is_array($data) && !empty($data)) {
                    $prepared = $wpdb->prepare($sql, $data);
                    $results = $wpdb->get_results($prepared);
                    unset($data, $prepared);
                } else {
                    $results = $wpdb->get_results($sql);
                }

                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    $output['debug_sql'] = $sql;
                    $output['debug_last_error'] = var_export($wpdb->last_error, true);
                    $output['debug_last_query'] = var_export($wpdb->last_query, true);
                }
                unset($sql);

                $cacheLifetime = apply_filters('rddownloads_cachelifetime_dashboardwidget_topdownloads', (3 * 60 * 60));// hours * minutes * seconds = total seconds.
                $SimpleCache->getInstance()->save($cacheKey, $results, $cacheLifetime);
                unset($cacheLifetime);
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    $output['cached_results'] = true;
                }
            }// endif; $results is not cached.

            unset($cacheKey, $SimpleCache);

            $output['total'] = (is_array($results) || is_object($results) ? count($results) : 0);
            $output['results'] = $results;

            unset($results, $scope);

            wp_send_json($output, 200);
        }// topDownloads


    }
}
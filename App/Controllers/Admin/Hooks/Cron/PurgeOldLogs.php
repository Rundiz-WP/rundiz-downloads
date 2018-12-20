<?php
/**
 * Purge old logs
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Hooks\Cron;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Hooks\\Cron\\PurgeOldLogs')) {
    class PurgeOldLogs implements \RdDownloads\App\Controllers\ControllerInterface
    {


        use \RdDownloads\App\AppTrait;


        /**
         * Purge old logs from DB.
         * 
         * @global array $rd_downloads_options
         * @global \wpdb $wpdb
         * @return boolean
         */
        public function purgeLogs()
        {
            $this->getOptions();
            global $rd_downloads_options;

            if (isset($rd_downloads_options['rdd_auto_delete_logs']) && $rd_downloads_options['rdd_auto_delete_logs'] != '1') {
                // if setting is not to auto purge old logs.
                return false;
            }

            if (isset($rd_downloads_options['rdd_auto_delete_logs_days']) && is_numeric($rd_downloads_options['rdd_auto_delete_logs_days'])) {
                $days = $rd_downloads_options['rdd_auto_delete_logs_days'];
            } else {
                $days = 90;
            }
            $current_datetime_gmt = current_time('mysql', true);

            // purge old logs from DB.
            global $wpdb;
            $result = $wpdb->query(
                $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'rd_download_logs WHERE dl_date_gmt < DATE_SUB(%s, INTERVAL %d DAY)', $current_datetime_gmt, $days)
            );

            if ($result !== false && $result > 0) {
                $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
                $RdDownloadLogs->writeLog('auto_purge_old_logs');
                unset($RdDownloadLogs);
            } elseif ($result === false) {
                error_log(
                    sprintf(
                        /* translators: %1$s: The last query statement, %2$s: MySQL error message. */
                        __('An error has been occur in SQL statement (%1$s). The error message: %2$s .'),
                        $wpdb->last_query,
                        $wpdb->last_error
                    )
                );
            }

            unset($current_datetime_gmt, $days, $result);
        }// purgeLogs


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('rddownloads_cron_purgelogs', [$this, 'purgeLogs']);

            if (!wp_next_scheduled('rddownloads_cron_purgelogs')) {
                wp_schedule_event(time(), 'daily', 'rddownloads_cron_purgelogs');
            }
        }// registerHooks


    }
}
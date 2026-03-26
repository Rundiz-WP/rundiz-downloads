<?php
/**
 * Version 1.0.18 update sub progress 1.
 * 
 * @package rundiz-downloads
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace RundizDownloads\App\Update\Manual;


if (!class_exists('\\RundizDownloads\\App\\Update\\Manual\\V1d0d18Sub1')) {
    /**
     * Version 1.0.18 update sub progress 1.
     */
    class V1d0d18Sub1 implements \RundizDownloads\App\Update\Manual\ManualUpdateInterface
    {


        public $manual_update_version = '0.3';


        /**
         * Rename tables.
         * 
         * @param \wpdb $wpdb The `\wpdb` object.
         */
        private function renameTables(\wpdb $wpdb)
        {
            $table_map = [
                'rd_downloads' => 'rundiz_downloads',
                'rd_download_logs' => 'rundiz_download_logs',
            ];

            $prefix = $wpdb->prefix;

            foreach ($table_map as $old_base => $new_base) {
                $old_table = $prefix . $old_base;
                $new_table = $prefix . $new_base;

                // Check if old table exists and new table does NOT exist yet
                $old_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $old_table));
                $new_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $new_table));

                if ($old_exists && !$new_exists) {
                    $wpdb->query("RENAME TABLE `{$old_table}` TO `{$new_table}`");

                    error_log("Rundiz Downloads: Renamed table {$old_table} → {$new_table}");
                }

                // If both exist → already migrated (or manual intervention happened)
                // If neither exists → fresh install, nothing to do
            }// endforeach;
            unset($new_base, $old_base);

            error_log('Rundiz Downloads: Table migration completed for site ' . get_current_blog_id());
        }// renameTables


        /**
         * {@inheritDoc}
         */
        public function run()
        {
            /* @var $wpdb \wpdb */
            global $wpdb;

            $is_multisite = is_multisite();

            if ($is_multisite) {
                $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
                $original_blog_id = get_current_blog_id();
            } else {
                $blog_ids = [get_current_blog_id()];
            }// endif;

            foreach ($blog_ids as $blog_id) {
                if ($is_multisite) {
                    switch_to_blog($blog_id);
                }// endif;

                $this->renameTables($wpdb);
            }// endforeach;
            unset($blog_id, $blog_ids);

            if ($is_multisite) {
                switch_to_blog($original_blog_id);
            }

            unset($is_multisite, $original_blog_id);
        }// run


    }// V1d0d18Sub1
}

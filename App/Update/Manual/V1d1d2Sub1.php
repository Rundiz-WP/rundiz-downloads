<?php
/**
 * Version 1.1.2
 * 
 * @package rundiz-downloads
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace RundizDownloads\App\Update\Manual;


if (!class_exists('\\RundizDownloads\App\Update\Manual\\V1d1d2Sub1')) {
    /**
     * Version 1.1.2 update sub progress 1.
     */
    class V1d1d2Sub1 implements \RundizDownloads\App\Update\Manual\ManualUpdateInterface
    {


        /**
         * @var string Manual update version.
         */
        public $manual_update_version = '0.4';


        /**
         * {@inheritDoc}
         */
        public function run()
        {
            // delete old version cron.  --------------------------------------------------------------
            // the new version using cron named `rundiz_downloads_cron_purgelogs`.
            $blog_ids = get_sites(['fields' => 'ids', 'number' => 0]);
            $original_blog_id = get_current_blog_id();
            if ($blog_ids) {
                foreach ($blog_ids as $blog_id) {
                    switch_to_blog($blog_id);
                    wp_clear_scheduled_hook('rddownloads_cron_purgelogs');
                }
            }
            switch_to_blog($original_blog_id);
            unset($blog_id, $blog_ids, $original_blog_id);
            // end delete old version cron.  ----------------------------------------------------------
        }// run


    }// V1d1d2Sub1
}

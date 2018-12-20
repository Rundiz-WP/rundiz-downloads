<?php
/**
 * The plugin database structure for use on activation.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Models;

if (!class_exists('\\RdDownloads\\App\\Models\\PluginDbStructure')) {
    class PluginDbStructure
    {


        /**
         * Get the database structure for use on activate this plugin.
         * 
         * The db schema will be use by WordPress Db Delta.
         * If you do not want to create any tables for this plugin then set this method to return empty array.
         * Please read more about db version at \RdDownloads\App\AppTrait->db_version property.
         * 
         * Limitation:
         * - DO NOT use back tick (`) anywhere because it will be thrown the error.
         * - DO NOT add "IF NOT EXISTS" into "CREATE TABLE" because it will not get an update on structure changed.
         * 
         * Example:
         * <pre>
         * $schema[0]['tablename'] = 'plugin_template1';
         * $schema[0]['statement'] = 'CREATE TABLE %PREFIX%%TABLE% (
         * id bigint(20) NOT NULL AUTO_INCREMENT,
         * PRIMARY KEY (id)
         * ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';// no back tick (`) to prevent error.
         * $schema[0]['is_multisite'] = false;// by default it is set to false (not multisite tables).
         * 
         * $schema[1]['tablename'] = 'plugin_template2';
         * $schema[1]['statement'] = 'CREATE TABLE ...';
         * $schema[1]['is_multisite'] = true;
         * </pre>
         * 
         * @return array Return array for each table that will be use for create. If you do not want db then set this method to return empty array.
         */
        public function get()
        {
            $schema = [];

            $schema[0]['tablename'] = 'rd_downloads';
            $schema[0]['statement'] = "CREATE TABLE %PREFIX%%TABLE% (
              download_id bigint(20) NOT NULL AUTO_INCREMENT,
              user_id bigint(20) DEFAULT NULL COMMENT 'Refer to users.ID of who create this.',
              download_name varchar(255) DEFAULT NULL COMMENT 'Downloads name.',
              download_admin_comment text DEFAULT NULL COMMENT 'Downloads commentation for administrator use only.',
              download_type int(1) NOT NULL DEFAULT 0 COMMENT 'Downloads file type: 0=local, 1=github, 2=any remote host',
              download_github_name varchar(255) DEFAULT NULL COMMENT 'GitHub repository name. For example: https://github.com/myaccount/myrepository will be myaccount/myrepository',
              download_url text DEFAULT NULL COMMENT 'Download file URL (for local and other type).',
              download_related_path text DEFAULT NULL COMMENT 'Downloads related path from wp-content/upload folder (for local only).',
              download_size int(11) NOT NULL DEFAULT 0 COMMENT 'Downloads file size.',
              download_file_name varchar(255) DEFAULT NULL COMMENT 'Download file name only. Example: myfile.zip',
              download_count int(11) NOT NULL DEFAULT 0 COMMENT 'Downloads count.',
              download_options longtext DEFAULT NULL COMMENT 'Serialize array of download options.',
              download_create datetime DEFAULT NULL COMMENT 'Add date/time.',
              download_create_gmt datetime DEFAULT NULL COMMENT 'Add date/time in GMT.',
              download_update datetime DEFAULT NULL COMMENT 'Last update.',
              download_update_gmt datetime DEFAULT NULL COMMENT 'Last update in GMT.',
              PRIMARY KEY (download_id),
              KEY user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='contain downloads data.' AUTO_INCREMENT=1 ;";
            $schema[0]['is_multisite'] = true;
            
            $schema[1]['tablename'] = 'rd_download_logs';
            $schema[1]['statement'] = "CREATE TABLE %PREFIX%%TABLE% (
              dl_id bigint(20) NOT NULL AUTO_INCREMENT,
              download_id bigint(20) DEFAULT NULL COMMENT 'Refer to rd_downloads.download_id',
              user_id bigint(20) DEFAULT NULL COMMENT 'Refer to users.ID',
              dl_cookie varchar(255) DEFAULT NULL COMMENT 'Downloads cookie for non-member.',
              dl_status varchar(20) DEFAULT NULL COMMENT 'Download status such as ''success'', ''error'', ''banned''.',
              dl_ip varchar(50) DEFAULT NULL COMMENT 'Downloader IP address.',
              dl_user_agent varchar(255) DEFAULT NULL COMMENT 'Downloader user agent.',
              dl_date datetime DEFAULT NULL COMMENT 'Download date/time.',
              dl_date_gmt datetime DEFAULT NULL COMMENT 'Download date/time in GMT.',
              PRIMARY KEY (dl_id),
              KEY download_id (download_id),
              KEY user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='contain download logs.' AUTO_INCREMENT=1 ;";
            $schema[1]['is_multisite'] = true;

            return $schema;
        }// get


    }
}
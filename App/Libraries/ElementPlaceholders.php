<?php
/**
 * Element placeholders.
 *
 * @package rd-downloads
 */

namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\ElementPlaceholders')) {
    class ElementPlaceholders
    {


        /**
         * All available table fields placeholders.
         * 
         * @return array
         */
        public function dbPlaceholders()
        {
            return [
                'download_name',
                'download_github_name',
                'download_url',
                'download_size',
                'download_file_name',
                'download_count',
                'opt_download_version',
                'download_create',
                'download_create_gmt',
                'download_update',
                'download_update_gmt',
            ];
        }// dbPlaceholders


        /**
         * Default download HTML element for replace with shortcode.
         * 
         * @return string Return HTML element for replace with shortcode.
         */
        public function defaultDownloadHtml()
        {
            return '<a class="rd-downloads-button" href="{{download_url}}"><i class="fontawesome-icon fas fa-download"></i> {{txt_download}}</a>';
        }// defaultDownloadHtml


        /**
         * All available text placeholders.
         * 
         * @return array
         */
        public function textPlaceholders()
        {
            return [
                'txt_download' => __('Download', 'rd-downloads'),
                'txt_download_name' => __('Downloads name', 'rd-downloads'),
                'txt_github_name' => __('GitHub repository name', 'rd-downloads'),
                'txt_size' => __('Size', 'rd-downloads'),
                'txt_file_name' => __('File name', 'rd-downloads'),
                'txt_file_size' => __('File size', 'rd-downloads'),
                'txt_create_on' => __('Create on', 'rd-downloads'),
                'txt_last_update' => __('Last update', 'rd-downloads'),
                'txt_total_download' => __('Total download', 'rd-downloads'),
                'txt_version' => __('Version', 'rd-downloads'),
            ];
        }// textPlaceholders


    }
}
<?php
/**
 * Bulk actions for logs.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Downloads\Xhr;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Downloads\\Xhr\\XhrLogsBulkActions')) {
    class XhrLogsBulkActions extends \RdDownloads\App\Controllers\XhrBased implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Get the selected bulk action and process to the selected items.
         */
        public function bulkActions()
        {
            $this->commonAccessCheck(['post'], ['rd-downloads_ajax-manage-nonce', 'security']);

            // check the most basic capability (permission).
            if (!current_user_can('upload_files')) {
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('You do not have permission to access this page.');
                wp_send_json($output, 403);
            }

            $bulkAction = filter_input(INPUT_POST, 'bulkAction');
            if (is_string($bulkAction)) {
                $bulkAction = strip_tags($bulkAction);
            }

            switch ($bulkAction) {
                case 'clearlogs':
                    return $this->clearLogs();
            }// endswitch;
            unset($bulkAction);

            $output['form_result_class'] = 'notice-error';
            $output['form_result_msg'] = __('Invalid form action, please try again.', 'rd-downloads');
            wp_send_json($output, 400);
        }// bulkActions


        /**
         * Clear the logs.
         *
         * This method will response json and end process.
         */
        protected function clearLogs()
        {
            // check the most basic capability (permission).
            if (!current_user_can('delete_users')) {
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('You do not have permission to access this page.');
                wp_send_json($output, 403);
            }

            $responseStatus = 200;
            $output = [];

            $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
            $clearResult = $RdDownloadLogs->clearLogs();
            unset($RdDownloadLogs);

            if (isset($clearResult['delete_error'])) {
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = $clearResult['delete_error'];
            } else {
                $output['form_result_class'] = 'notice-success';
                $output['form_result_msg'] = __('All logs were cleared.', 'rd-downloads');
            }

            wp_send_json($output, $responseStatus);
        }// clearLogs


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdDownloadsLogsBulkActions', [$this, 'bulkActions']);
            }
        }// registerHooks


    }
}

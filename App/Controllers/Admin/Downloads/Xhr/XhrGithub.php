<?php
/**
 * GitHub data.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Downloads\Xhr;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Downloads\\Xhr\\XhrGithub')) {
    class XhrGithub extends \RdDownloads\App\Controllers\XhrBased implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Get GitHub file data.
         */
        public function getGithubFileData()
        {
            $this->commonAccessCheck(['get'], ['rd-downloads_ajax-file-browser-nonce', 'security']);

            $output = [];
            $responseStatus = 200;
            $remote_file = trim(filter_input(INPUT_GET, 'remote_file', FILTER_SANITIZE_URL));

            if (stripos($remote_file, 'github.com/') === false) {
                $responseStatus = 400;
                $output['form_result_class'] = 'notice-error';
                /* translators: %s: Example GitHub repository URL. */
                $output['form_result_msg'] = sprintf(__('Invalid GitHub repository URL. The correct format should be %s.', 'rd-downloads'), 'https://github.com/owner/name');
            } else {
                $Github = new \RdDownloads\App\Libraries\Github();
                $result = $Github->getLatestRepositoryData($remote_file);
                unset($Github);
                if (is_array($result)) {
                    $output = $output + $result;
                } elseif ($result === false) {
                    $responseStatus = 400;
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = sprintf(__('Invalid GitHub repository URL. The correct format should be %s.', 'rd-downloads'), 'https://github.com/owner/name');
                }
            }

            wp_send_json($output, $responseStatus);
        }// getGithubFileData


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdDownloadsGetGithubFileData', [$this, 'getGithubFileData']);
            }
        }// registerHooks


    }
}
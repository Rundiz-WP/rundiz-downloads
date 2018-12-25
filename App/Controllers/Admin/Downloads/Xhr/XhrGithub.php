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
            $current_version = trim(filter_input(INPUT_GET, 'current_version', FILTER_SANITIZE_STRING));
            $version_range = trim(filter_input(INPUT_GET, 'version_range', FILTER_SANITIZE_STRING));

            $Semver = new \RdDownloads\App\Libraries\Semver();
            if ((is_null($version_range) || $version_range === '')) {
                $version_range = $Semver->getDefaultVersionConstraint($current_version);
            }
            unset($Semver);

            if (stripos($remote_file, 'github.com/') === false) {
                $responseStatus = 400;
                $output['form_result_class'] = 'notice-error';
                /* translators: %s: Example GitHub repository URL. */
                $output['form_result_msg'] = sprintf(__('Invalid GitHub repository URL. The correct format should be %s.', 'rd-downloads'), 'https://github.com/owner/name');
            } else {
                $Github = new \RdDownloads\App\Libraries\Github();
                $result = $Github->getLatestRepositoryData($remote_file, $version_range);
                unset($Github);
                if (is_array($result)) {
                    $output = $output + $result;
                } elseif ($result === false) {
                    // if cannot get repository data.
                    // return as-is in case the user input some url on GitHub.
                    $responseStatus = 202;
                    $output['url'] = $remote_file;
                }
            }

            unset($current_version, $remote_file, $version_range);

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
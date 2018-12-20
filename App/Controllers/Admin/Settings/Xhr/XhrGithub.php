<?php
/**
 * Settings > Xhr GitHub.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Settings\Xhr;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Settings\\Xhr\\XhrGithub')) {
    class XhrGithub extends \RdDownloads\App\Controllers\XhrBased implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdDownloadsSettingsTestGithubToken', [$this, 'testGithubToken']);
            }
        }// registerHooks


        /**
         * Ajax test GitHub token.
         */
        public function testGithubToken()
        {
            $this->commonAccessCheck(['post'], ['rd-downloads-settings_ajax-settings-nonce', 'security']);

            $output = [];
            $responseStatus = 200;

            $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

            $headers = [
                'Authorization: bearer ' . $token,
            ];
            // @link https://developer.github.com/v4/explorer/ query explorer.
            $postData = [
                'query' => 'query {
                    viewer {
                        login
                    }
                }'
            ];
            $postData = wp_json_encode($postData);

            $Github = new \RdDownloads\App\Libraries\Github();
            $result = $Github->apiRequest($headers, $postData);
            unset($Github, $headers, $postData);

            if (is_object($result) && isset($result->data->viewer->login)) {
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('Correct!', 'rd-downloads');
            } else {
                $responseStatus = 403;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('Invalid GitHub token.', 'rd-downloads');
            }

            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                $output['debugResult'] = $result;
            }
            unset($result);

            wp_send_json($output, $responseStatus);
        }// testGithubToken


    }
}
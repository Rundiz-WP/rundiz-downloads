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
         * @var \RdDownloads\App\Libraries\Github GitHub class.
         */
        protected $Github;


        public function __construct()
        {
            $this->Github = new \RdDownloads\App\Libraries\Github();
        }// __construct


        /**
         * Check that if there is webhook for this site on GitHub repository or not.
         *
         * If there is get its hook_id and the result.
         */
        public function checkGitHubWebhook()
        {
            $this->commonAccessCheck(['get'], ['rd-downloads_github-api-nonce', 'security']);

            $output = [];
            $responseStatus = 200;

            $namewithowner = filter_input(INPUT_GET, 'namewithowner');
            if (is_string($namewithowner)) {
                $namewithowner = htmlspecialchars($namewithowner, ENT_QUOTES);
            }
            $expNameWithOwner = explode('/', $namewithowner);
            $repoOwner = $expNameWithOwner[0];
            unset($expNameWithOwner[0], $namewithowner);
            $repoName = implode('/', $expNameWithOwner);
            unset($expNameWithOwner);

            $accessToken = $this->Github->getOAuthAccessToken();
            $headers = $this->Github->apiV3Headers($accessToken);
            unset($accessToken);

            $hook_id = $this->Github->apiGetWebhookId($headers, $repoOwner, $repoName);
            unset($headers, $repoName, $repoOwner);
            if ($hook_id === false) {
                $output['foundWebhook'] = false;
            } else {
                $output['foundWebhook'] = true;
                $output['hook_id'] = $hook_id;
            }
            unset($hook_id);

            wp_send_json($output, $responseStatus);
        }// checkGitHubWebhook


        /**
         * Get GitHub file data.
         */
        public function getGithubFileData()
        {
            $this->commonAccessCheck(['get'], ['rd-downloads_ajax-file-browser-nonce', 'security']);

            $output = [];
            $responseStatus = 200;
            $remote_file = trim(filter_input(INPUT_GET, 'remote_file', FILTER_SANITIZE_URL));
            $current_version = trim(filter_input(INPUT_GET, 'current_version'));
            if (is_string($current_version)) {
                $current_version = strip_tags($current_version);
            }
            $version_range = trim(filter_input(INPUT_GET, 'version_range'));
            if (is_string($version_range)) {
                $version_range = strip_tags($version_range);
            }

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
                $result = $this->Github->apiGetLatestRepositoryData($remote_file, $version_range);
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
                add_action('wp_ajax_RdDownloadsNewGitHubSecret', [$this, 'updateGitHubSecret']);
                add_action('wp_ajax_RdDownloadsSyncGitHubSecretToAll', [$this, 'syncGitHubSecretToAllDownloads']);
                add_action('wp_ajax_RdDownloadsCheckGitHubWebhook', [$this, 'checkGitHubWebhook']);
            }
        }// registerHooks


        /**
         * Synchonize secret on GitHub.
         *
         * @link https://developer.github.com/v3/repos/hooks/ repo hook reference.
         */
        public function syncGitHubSecretToAllDownloads()
        {
            $this->commonAccessCheck(['post'], ['rd-downloads_github-api-nonce', 'security']);

            $output = [];
            $responseStatus = 200;

            $user_id = get_current_user_id();

            $options = [];
            $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
            $options['user_id'] = $user_id;
            $options['download_type'] = 1;
            $options['*unlimit'] = true;
            $rdDownloadsResult = $RdDownloads->listItems($options);
            unset($options, $RdDownloads);

            $totalSynced = 0;// total synced.
            $totalNewSynced = 0;// total newly add webhook.
            $totalUpdateSynced = 0;// total update webhook to the exists.
            $totalDownloads = 0;

            if (isset($rdDownloadsResult['total'])) {
                $totalDownloads = $rdDownloadsResult['total'];
            }

            if (isset($rdDownloadsResult['results']) && is_array($rdDownloadsResult['results'])) {
                set_time_limit((10 * 60));// minutes * 60 seconds = total seconds.
                $output['hook_ids'] = [];
                $accessToken = $this->Github->getOAuthAccessToken($user_id);
                $secretKey = $this->Github->getWebhookSecretKey($user_id);

                if (!empty($accessToken) && !empty($secretKey)) {
                    foreach ($rdDownloadsResult['results'] as $row) {
                        if ($row->download_github_name != null) {
                            $expNameWithOwner = explode('/', $row->download_github_name);
                            $repoOwner = $expNameWithOwner[0];
                            unset($expNameWithOwner[0]);
                            $repoName = implode('/', $expNameWithOwner);
                            unset($expNameWithOwner);

                            if (empty($repoName) || empty($repoOwner)) {
                                // no repo name or owner, skip it.
                                continue;
                            }

                            $headers = $this->Github->apiV3Headers($accessToken);

                            // get current hooks to check that there is webhook for this site already or not.
                            $hook_id = $this->Github->apiGetWebhookId($headers, $repoOwner, $repoName);
                            if ($hook_id === false) {
                                unset($hook_id);
                            }

                            if (!isset($hook_id)) {
                                // if there is no webhook on this GitHub repository.
                                // add new hook.
                                $response = $this->Github->apiAddUpdateGitHubWebhook($user_id, '', $secretKey, $repoOwner, $repoName, $headers);
                                if (isset($response['body']->id)) {
                                    $hook_id = $response['body']->id;
                                    $totalSynced++;
                                    $totalNewSynced++;
                                }
                            } else {
                                // if there is webhook on this GitHub repository already.
                                // update hook.
                                $output['hook_ids'][] = $hook_id;
                                $response = $this->Github->apiAddUpdateGitHubWebhook($user_id, $hook_id, $secretKey, $repoOwner, $repoName, $headers);
                                $totalSynced++;
                                $totalUpdateSynced++;
                            }

                            unset($headers, $hook_id, $repoName, $repoOwner, $response);
                        }
                    }// endforeach;
                    unset($row);
                } else {
                    if (defined('WP_DEBUG') && WP_DEBUG === true) {
                        $output['debug_accessToken_and_secretKey_empty'] = true;
                        $output['debug_accessToken'] = $accessToken;
                        $output['debug_secretKey'] = $secretKey;
                    }
                }// endif; empty token or secret key.
                unset($accessToken, $secretKey);
            }// endif there is rddownloads result

            unset($rdDownloadsResult);

            $output['totalDownloads'] = $totalDownloads;
            $output['totalSynced'] = $totalSynced;
            $output['totalNewSynced'] = $totalNewSynced;
            $output['totalUpdateSynced'] = $totalUpdateSynced;

            $output['form_result_class'] = 'notice-success';
            /* translators: %1$d: Total synced, %2$d: Total new webhooks, %3$d: Total existing webhooks (use update), %4$d: Total download items. */
            $output['form_result_msg'] = sprintf(
                __('Synced successfully. Total %1$d synced (%2$d new, %3$d exists) from total %4$d download items.'),
                $totalSynced,
                $totalNewSynced,
                $totalUpdateSynced,
                $totalDownloads
            );

            wp_send_json($output, $responseStatus);
        }// syncGitHubSecretToAllDownloads


        /**
         * Update GitHub secret.
         *
         * @global \wpdb $wpdb
         */
        public function updateGitHubSecret()
        {
            $this->commonAccessCheck(['post'], ['rd-downloads_github-api-nonce', 'security']);

            $output = [];
            $responseStatus = 200;

            $user_id = get_current_user_id();
            $rddownloads_githubwebhook_secret = filter_input(INPUT_POST, 'rddownloads_githubwebhook_secret');
            if (is_string($rddownloads_githubwebhook_secret)) {
                $rddownloads_githubwebhook_secret = htmlspecialchars($rddownloads_githubwebhook_secret, ENT_QUOTES);
            }
            if (mb_strlen(trim($rddownloads_githubwebhook_secret)) < 20) {
                $output['secretLengthFailed'] = true;
                $output['secretLength'] = mb_strlen(trim($rddownloads_githubwebhook_secret));
                $output['oldSecret'] = $rddownloads_githubwebhook_secret;
                $rddownloads_githubwebhook_secret = $this->Github->generateWebhookSecretKey($user_id);
            }

            $output['updateResult'] = update_user_meta($user_id, $this->Github->getWebhookSecretName(), $rddownloads_githubwebhook_secret);
            $output['updated'] = ($output['updateResult'] === false ? false : true);
            $output['githubSecret'] = $rddownloads_githubwebhook_secret;
            unset($rddownloads_githubwebhook_secret, $user_id);

            if ($output['updated'] === false) {
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('Unable to update secret key, please reload the page and try again.', 'rd-downloads');

                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    global $wpdb;
                    $output['debug_updateFailedMsg'] = $wpdb->last_error;
                }
            } else {
                $output['form_result_class'] = 'notice-success';
                $output['form_result_msg'] = __('The secret key was updated successfully.', 'rd-downloads');
            }

            wp_send_json($output, $responseStatus);
        }// updateGitHubSecret


    }
}
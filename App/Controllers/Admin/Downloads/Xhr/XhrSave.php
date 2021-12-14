<?php
/**
 * Saving the data to DB. (including insert, update).
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Downloads\Xhr;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Downloads\\Xhr\\XhrSave')) {
    class XhrSave extends \RdDownloads\App\Controllers\XhrBased implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Automatically add GitHub webhook "if not exists".
         *
         * This method was called from `saveInsertData()`, `saveUpdateData()` methods.
         *
         * @param array $data The data same as prepared for save.
         */
        private function addGitHubWebhook(array $data)
        {
            if (!isset($data['download_type']) || (isset($data['download_type']) && $data['download_type'] != 1)) {
                // if not GitHub.
                return ;
            }

            if (!isset($data['download_github_name']) || (isset($data['download_github_name']) && empty($data['download_github_name']))) {
                // if there is no GitHub name.
                return ;
            }

            $Github = new \RdDownloads\App\Libraries\Github();

            $user_id = (isset($data['user_id']) && !empty($data['user_id']) ? $data['user_id'] : get_current_user_id());
            $accessToken = $Github->getOAuthAccessToken($user_id);
            $secretKey = $Github->getWebhookSecretKey($user_id);

            if (empty($accessToken) || empty($secretKey)) {
                // if there is no access token or secret key.
                // do nothing
                unset($accessToken, $Github, $secretKey, $user_id);
                return ;
            }

            $expNameWithOwner = explode('/', $data['download_github_name']);
            $repoOwner = $expNameWithOwner[0];
            unset($expNameWithOwner[0]);
            $repoName = implode('/', $expNameWithOwner);
            unset($expNameWithOwner);

            $headers = $Github->apiV3Headers($accessToken);
            $hook_id = $Github->apiGetWebhookId($headers, $repoOwner, $repoName);

            if ($hook_id !== false) {
                // if already have webhook.
                unset($accessToken, $Github, $headers, $hook_id, $repoName, $repoOwner, $secretKey, $user_id);
                return ;
            }
            unset($hook_id);

            $result = $Github->apiAddUpdateGitHubWebhook($user_id, '', $secretKey, $repoOwner, $repoName, $headers);

            \RdDownloads\App\Libraries\Logger::staticDebugLog($result, 'github-api-add-webhook-on-save-download-data-' . current_time('Ymd-Hi'));
            unset($result);

            unset($accessToken, $Github, $headers, $repoName, $repoOwner, $secretKey, $user_id);
        }// addGitHubWebhook


        /**
         * Prepare input data.
         *
         * Also auto detect URL and then get additional data that will be insert/update to DB table.
         *
         * @return array|string Return array with data if success. Return string if there is an error message.
         */
        private function prepareInputData()
        {
            $data = [];
            $data['download_url'] = filter_input(INPUT_POST, 'download_url', FILTER_SANITIZE_URL);

            if (strpos($data['download_url'], '..') !== false) {
                return __('Hacking attempt!', 'rd-downloads');
            }

            $data['download_name'] = filter_input(INPUT_POST, 'download_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data['download_admin_comment'] = filter_input(INPUT_POST, 'download_admin_comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data['download_related_path'] = filter_input(INPUT_POST, 'download_related_path', FILTER_UNSAFE_RAW);
            $data['download_size'] = filter_input(INPUT_POST, 'download_size', FILTER_SANITIZE_NUMBER_INT);
            if ($data['download_size'] == null) {
                $data['download_size'] = 0;
            }
            $data['download_count'] = filter_input(INPUT_POST, 'download_count', FILTER_SANITIZE_NUMBER_INT);
            if (empty($data['download_count'])) {
                $data['download_count'] = 0;
            }
            $data['download_update'] = current_time('mysql');
            $data['download_update_gmt'] = current_time('mysql', true);

            $Url = new \RdDownloads\App\Libraries\Url();
            $domainNoSub = strtolower($Url->getDomain($data['download_url']));
            $currentDomain = strtolower($Url->getDomain('http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . '/'));
            $FileSystem = new \RdDownloads\App\Libraries\FileSystem();
            if ($domainNoSub !== null && $domainNoSub !== false) {
                // if can get domain without sub domain from the specific URL.
                $opt_force_download = filter_input(INPUT_POST, 'opt_force_download', FILTER_SANITIZE_NUMBER_INT);
                $opt_download_version = filter_input(INPUT_POST, 'opt_download_version');
                if (is_string($opt_download_version)) {
                    $opt_download_version = strip_tags($opt_download_version);
                }
                $opt_download_version_range = filter_input(INPUT_POST, 'opt_download_version_range');
                if (is_string($opt_download_version_range)) {
                    $opt_download_version_range = htmlspecialchars($opt_download_version_range, ENT_QUOTES);
                }
                $data['download_options'] = maybe_serialize(
                    [
                        'opt_force_download' => $opt_force_download,
                        'opt_download_version' => $opt_download_version,
                        'opt_download_version_range' => $opt_download_version_range,
                    ]
                );
                unset($opt_force_download);

                switch ($domainNoSub) {
                    case 'github.com':
                        // for github domain.
                        $data['download_type'] = 1;

                        $Semver = new \RdDownloads\App\Libraries\Semver();
                        $version_range = $opt_download_version_range;
                        if ((is_null($version_range) || $version_range === '')) {
                            $version_range = $Semver->getDefaultVersionConstraint($opt_download_version);
                        }
                        unset($Semver);

                        $Github = new \RdDownloads\App\Libraries\Github();
                        $nameWithOwner = $Github->getNameWithOwnerFromUrl($data['download_url']);
                        unset($Github);
                        if (is_array($nameWithOwner) && isset($nameWithOwner[0]) && isset($nameWithOwner[1])) {
                            // if can get GitHub name with owner.
                            $data['download_github_name'] = $nameWithOwner[0] . '/' . $nameWithOwner[1];
                            if (empty($data['download_name'])) {
                                $data['download_name'] = $nameWithOwner[0] . '/' . $nameWithOwner[1];
                            }
                        } else {
                            // if cannot get GitHub name with owner. mark this to null and set download type to other remote URL (type 2).
                            $data['download_github_name'] = null;
                            $data['download_type'] = 2;
                        }
                        unset($nameWithOwner);

                        $data['download_related_path'] = null;
                        break;
                    case $currentDomain:
                    default:
                        // for local and other remote domains.
                        $data['download_github_name'] = null;

                        if ($domainNoSub === $currentDomain) {
                            // if domain without sub domain matched current domain.
                            $data['download_type'] = 0;

                            $wp_upload_dir = wp_upload_dir();
                            if (isset($wp_upload_dir['baseurl'])) {
                                $relatedPathUrl = str_replace(trailingslashit($wp_upload_dir['baseurl']), '', $data['download_url']);
                                if ($relatedPathUrl != $data['download_related_path']) {
                                    // if related path is missing from manual enter path that is on this server.
                                    $data['download_related_path'] = $relatedPathUrl;
                                }
                                unset($relatedPathUrl);
                            }
                            unset($wp_upload_dir);
                        } else {
                            // if domain without sub domain does not match current domain.
                            $data['download_type'] = 2;
                            $data['download_related_path'] = null;
                        }
                        break;
                }// endswitch;

                unset($opt_download_version, $opt_download_version_range);

                // set common data for each download type.
                $fileParts = $FileSystem->getFilePart($data['download_url']);
                if (isset($fileParts['nameext'])) {
                    $data['download_file_name'] = $fileParts['nameext'];
                }
                unset($fileParts);

                if (empty($data['download_name']) && isset($data['download_file_name']) && !empty($data['download_file_name'])) {
                    // if download name is empty, set it to file name (just name with extension).
                    $data['download_name'] = $data['download_file_name'];
                }

                $opt_force_download = filter_input(INPUT_POST, 'opt_force_download', FILTER_SANITIZE_NUMBER_INT);
                $opt_download_version = filter_input(INPUT_POST, 'opt_download_version');
                if (is_string($opt_download_version)) {
                    $opt_download_version = strip_tags($opt_download_version);
                }
                $opt_download_version_range = filter_input(INPUT_POST, 'opt_download_version_range');
                if (is_string($opt_download_version_range)) {
                    $opt_download_version_range = htmlspecialchars($opt_download_version_range, ENT_QUOTES);
                }
                $data['download_options'] = maybe_serialize(
                    [
                        'opt_force_download' => $opt_force_download,
                        'opt_download_version' => $opt_download_version,
                        'opt_download_version_range' => $opt_download_version_range,
                    ]
                );
                unset($opt_download_version, $opt_download_version_range, $opt_force_download);
            } else {
                // if cannot get domain without sub domain from the specific URL.
                unset($currentDomain, $domainNoSub, $FileSystem, $Url);
                return __('Incorrect Download URL value.', 'rd-downloads');
            }// endif; $domainNoSub.
            unset($currentDomain, $domainNoSub, $FileSystem, $Url);

            return $data;
        }// prepareInputData


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdDownloadsSaveData', [$this, 'saveData']);
            }
        }// registerHooks


        /**
         * Determine saving data to be insert or update and call to its function.
         *
         * @return void
         */
        public function saveData()
        {
            $this->commonAccessCheck(['post'], ['rd-downloads-ajax-saving-nonce', 'security']);

            if (!current_user_can('upload_files')) {
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('You do not have permission to access this page.');
                wp_send_json($output, 403);
            }

            $download_id = filter_input(INPUT_POST, 'download_id', FILTER_SANITIZE_NUMBER_INT);

            if (empty($download_id) || $download_id <= '0') {
                // use insert.
                $this->saveInsertData();
            } else {
                // use update.
                $this->saveUpdateData();
            }
        }// saveData


        /**
         * Save new data (insert) to DB.
         *
         * @global \wpdb $wpdb
         */
        protected function saveInsertData()
        {
            $responseStatus = 201;
            $output = [];

            // prepare data for validate & save.
            $data = [];
            $data['user_id'] = get_current_user_id();
            $data['download_create'] = current_time('mysql');
            $data['download_create_gmt'] = current_time('mysql', true);

            $additionalData = $this->prepareInputData();
            if (is_array($additionalData) && !empty($additionalData)) {
                // if success get additional data from download url.
                $data = $data + $additionalData;

                // make empty as null.
                $Input = new \RdDownloads\App\Libraries\Input();
                $data = $Input->setNullIfDataValueEmpty($data);
                unset($Input);

                $validated = true;
            } else {
                // if contain error.
                $responseStatus = 400;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = (is_scalar($additionalData) ? $additionalData : __('Unknown error, please reload the webpage and try again.', 'rd-downloads'));
                $validated = false;
            }
            unset($additionalData);

            // ready to insert the data.
            if (isset($validated) && $validated === true) {
                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    $output['debugDataToSave'] = $data;
                }

                global $wpdb;
                $output['insertResult'] = $wpdb->insert($wpdb->prefix . 'rd_downloads', $data);
                if ($output['insertResult'] !== false) {
                    $output['download_id'] = $wpdb->insert_id;
                    $output['saved'] = true;
                    $output['editUrl'] = admin_url('admin.php?page=rd-downloads_edit&download_id=' . $output['download_id']);
                    $output['form_result_class'] = 'notice-success';
                    $output['form_result_msg'] = __('Your download was saved successfully.', 'rd-downloads');

                    $Dll = new \RdDownloads\App\Models\RdDownloadLogs();
                    $Dll->writeLog('admin_insert', [
                        'download_id' => $output['download_id'],
                    ]);
                    unset($Dll);

                    $this->addGitHubWebhook($data);
                } else {
                    $responseStatus = 500;
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = $wpdb->last_error;
                }
            }

            unset($data, $validated);

            // response with data.
            wp_send_json($output, $responseStatus);
        }// saveInsertData


        /**
         * Save update data to DB.
         *
         * @global \wpdb $wpdb
         */
        protected function saveUpdateData()
        {
            $responseStatus = 200;
            $output = [];

            // prepare data for validate & save.
            $data = [];
            $download_id = filter_input(INPUT_POST, 'download_id', FILTER_SANITIZE_NUMBER_INT);

            // check first that is this user editing other's file or not.
            $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
            $checkResult = $RdDownloads->get(['*select' => 'user_id', 'download_id' => $download_id]);
            if (
                empty($checkResult) ||
                is_null($checkResult) ||
                (
                    (
                        is_array($checkResult) ||
                        is_object($checkResult)
                    ) &&
                    empty($checkResult)
                )
            ) {
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('The editing item was not found.', 'rd-downloads');
                wp_send_json($output, 404);
            } else {
                if (isset($checkResult->user_id) && $checkResult->user_id != get_current_user_id() && !current_user_can('edit_others_posts')) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('You do not have permission to access this page.');
                    wp_send_json($output, 403);
                }
            }
            unset($checkResult);

            $additionalData = $this->prepareInputData();
            if (is_array($additionalData) && !empty($additionalData)) {
                // if success get additional data from download url.
                $data = $data + $additionalData;

                // make empty as null.
                $Input = new \RdDownloads\App\Libraries\Input();
                $data = $Input->setNullIfDataValueEmpty($data);
                unset($Input);

                $validated = true;
            } else {
                // if contain error.
                $responseStatus = 400;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = (is_scalar($additionalData) ? $additionalData : __('Unknown error, please reload the webpage and try again.', 'rd-downloads'));
                $validated = false;
            }
            unset($additionalData);

            // ready to update the data.
            if (isset($validated) && $validated === true) {
                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    $output['debugDataToSave'] = $data;
                }

                $output['updateResult'] = $RdDownloads->update($data, ['download_id' => $download_id]);
                if ($output['updateResult'] !== false) {
                    $output['last_update'] = \RdDownloads\App\Libraries\DateTime::displayDateTime(current_time('mysql', true));
                    $output['saved'] = true;
                    $output['form_result_class'] = 'notice-success';
                    $output['form_result_msg'] = __('Your download was saved successfully.', 'rd-downloads');

                    $Dll = new \RdDownloads\App\Models\RdDownloadLogs();
                    $Dll->writeLog('admin_update', [
                        'download_id' => $download_id,
                    ]);
                    unset($Dll);

                    $this->addGitHubWebhook($data);

                    // clear all cache on save.
                    $Cache = new \RdDownloads\App\Libraries\Cache();
                    $output['cacheCleared'] = $Cache->getInstance()->clear();
                    unset($Cache);
                } else {
                    global $wpdb;
                    $responseStatus = 500;
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = $wpdb->last_error;
                }
            }

            unset($data, $download_id, $RdDownloads, $validated);

            // response with data.
            wp_send_json($output, $responseStatus);
        }// saveUpdateData


    }
}
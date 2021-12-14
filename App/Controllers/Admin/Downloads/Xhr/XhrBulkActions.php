<?php
/**
 * Bulk actions class.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Downloads\Xhr;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Downloads\\Xhr\\XhrBulkActions')) {
    class XhrBulkActions extends \RdDownloads\App\Controllers\XhrBased implements \RdDownloads\App\Controllers\ControllerInterface
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
            $download_ids = filter_input_array(INPUT_POST, [
                'download_id' => [
                    'filter' => FILTER_SANITIZE_NUMBER_INT,
                    'flags' => FILTER_REQUIRE_ARRAY,
                ],
            ]);
            if (is_array($download_ids) && array_key_exists('download_id', $download_ids)) {
                $download_ids = $download_ids['download_id'];
            }

            if (
                (
                    !is_array($download_ids) || 
                    (
                        is_array($download_ids) && empty($download_ids)
                    )
                )
            ) {
                status_header(400);
                exit();
            }

            if (is_array($download_ids)) {
                switch ($bulkAction) {
                    case 'githubUpdate':
                        return $this->githubUpdate($download_ids);
                    case 'remoteUpdate':
                        return $this->remoteUpdate($download_ids);
                    case 'delete':
                        return $this->deleteDownloads($download_ids);
                }// endswitch;
            }// endif;
            unset($bulkAction);

            $output['form_result_class'] = 'notice-error';
            $output['form_result_msg'] = __('Invalid form action, please try again.', 'rd-downloads');
            wp_send_json($output, 400);
        }// bulkActions


        /**
         * Perform delete downloads data.
         *
         * This method will response json and end process.
         *
         * @global \wpdb $wpdb
         * @param array $download_ids The `download_id` values in one array. Example: `array(1, 2, 4, 5, 6);`
         */
        protected function deleteDownloads(array $download_ids)
        {
            $responseStatus = 200;
            $output = [];

            // get the data from DB.
            global $wpdb;
            // use WHERE IN to search for *any* of the values. https://mariadb.com/kb/en/library/in/
            $sql = 'SELECT `download_id`, `user_id`, `download_name`, `download_type`, `download_github_name`, `download_related_path`
                FROM `' . $wpdb->prefix . 'rd_downloads`
                WHERE `download_id` IN (' . implode(', ', array_fill(0, count($download_ids), '%d')) . ')';// https://stackoverflow.com/a/10634225/128761
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    $sql,
                    $download_ids
                )
            );

            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                $output['debugSQL'] = $sql;// before executed sql statement (contain %s placeholder for prepare).
                $output['debugLastQuery'] = $wpdb->last_query;// executed sql statement.
                $output['download_ids'] = $download_ids;
            }
            unset($sql);

            if (count($results) > 0 && (is_array($results) || is_object($results))) {
                // if found the results.
                $current_user_id = get_current_user_id();

                $found_download_ids = [];
                $capability_limited_download_ids = [];
                $capability_limited_download_names = [];
                $deleted_download_ids = [];
                $deleted_download_names = [];
                $failed_delete_download_ids = [];
                $failed_delete_download_names = [];
                $failed_remove_githubwebhook = [];

                $FileSystem = new \RdDownloads\App\Libraries\FileSystem();
                $Github = new \RdDownloads\App\Libraries\Github();
                $accessToken = $Github->getOAuthAccessToken($current_user_id);
                $apiHeader = $Github->apiV3Headers($accessToken);
                unset($accessToken);

                foreach ($results as $row) {
                    $found_download_ids[] = $row->download_id;
                    if ($row->user_id != $current_user_id && !current_user_can('edit_others_posts')) {
                        // this user is trying to editing/updating others download and don't have capability to do it.
                        // this condition is unable to delete the data.
                        $capability_limited_download_ids[] = $row->download_id;
                        $capability_limited_download_names[] = $row->download_name;
                    } else {
                        // this condition is able to delete the data.
                        if ($row->download_type == '0' && stripos($row->download_related_path, 'rd-downloads/') !== false) {
                            // if local file.
                            // check again that this file is NOT linked with other downloads data.
                            $sql = 'SELECT COUNT(`download_id`) AS `total`, `download_id`, `download_related_path` FROM `' . $wpdb->prefix . 'rd_downloads` WHERE `download_related_path` = %s AND `download_id` != %d';
                            $checkExists = $wpdb->get_var($wpdb->prepare($sql, [$row->download_related_path, $row->download_id]));
                            unset($sql);
                            if (is_null($checkExists)) {
                                // if `get_var()` contain some error.
                                $failed_delete_download_ids[] = $row->download_id;
                                $failed_delete_download_names[] = $row->download_name;
                                $donot_delete = true;
                                error_log(
                                    sprintf(
                                        /* translators: %1$s: The last query statement, %2$s: MySQL error message. */
                                        __('An error has been occur in SQL statement (%1$s). The error message: %2$s .'),
                                        $wpdb->last_query,
                                        $wpdb->last_error
                                    )
                                );
                            } elseif ($checkExists <= 0) {
                                // if not exists in other download data, delete the file.
                                $wp_upload_dir = wp_upload_dir();
                                if (isset($wp_upload_dir['basedir'])) {
                                    $FileSystem->deleteFile(trailingslashit($wp_upload_dir['basedir']) . $row->download_related_path);
                                }
                                unset($wp_upload_dir);
                            }
                            unset($checkExists);
                        } elseif ($row->download_type == '1') {
                            // if github file.
                            // check that if there is no same github repo name on db.
                            $sql = 'SELECT COUNT(`download_id`) AS `total`, `download_id`, `download_github_name` FROM `' . $wpdb->prefix . 'rd_downloads` WHERE `download_github_name` = %s AND `download_id` != %d';
                            $checkExists = $wpdb->get_var($wpdb->prepare($sql, [$row->download_github_name, $row->download_id]));
                            unset($sql);
                            if (is_null($checkExists)) {
                                // if `get_var()` contain some error.
                                error_log(
                                    sprintf(
                                        /* translators: %1$s: The last query statement, %2$s: MySQL error message. */
                                        __('An error has been occur in SQL statement (%1$s). The error message: %2$s .'),
                                        $wpdb->last_query,
                                        $wpdb->last_error
                                    )
                                );
                            } elseif ($checkExists <= 0) {
                                // if not exists in other download data, mark as remove webhook.
                                $removeWebhook = true;
                            }
                            unset($checkExists);
                        }// endif download type.

                        if (!isset($donot_delete) || (isset($donot_delete) && $donot_delete === false)) {
                            // if it is able to delete, delete it in db.
                            $deleteResult = $wpdb->delete($wpdb->prefix . 'rd_downloads', ['download_id' => $row->download_id]);
                            if ($deleteResult !== false) {
                                $deleted_download_ids[] = $row->download_id;
                                $deleted_download_names[] = $row->download_name;
                                $Dll = new \RdDownloads\App\Models\RdDownloadLogs();
                                $Dll->writeLog('admin_delete', [
                                    'download_id' => $row->download_id,
                                ]);
                                unset($Dll);

                                if (isset($removeWebhook) && $removeWebhook === true) {
                                    // if it was marked as remove webhook.
                                    $expNameOwner = explode('/', $row->download_github_name);
                                    $repoOwner = $expNameOwner[0];
                                    unset($expNameOwner[0]);
                                    $repoName = implode('/', $expNameOwner);
                                    unset($expNameOwner);

                                    $hook_id = $Github->apiGetWebhookId($apiHeader, $repoOwner, $repoName);

                                    if ($hook_id !== false) {
                                        $removeWebhookResult = $Github->apiRemoveWebhook($hook_id, $repoOwner, $repoName, $apiHeader);
                                        if (
                                            !isset($removeWebhookResult['header']['status-int']) ||
                                            (
                                                isset($removeWebhookResult['header']['status-int']) &&
                                                (
                                                    $removeWebhookResult['header']['status-int'] < 200 ||
                                                    $removeWebhookResult['header']['status-int'] >= 300
                                                )
                                            )
                                        ) {
                                            // if failed to remove.
                                            $failed_remove_githubwebhook[] = $row->download_github_name;
                                        }
                                        unset($removeWebhookResult);
                                    }
                                    unset($hook_id, $repoName, $repoOwner);
                                }
                            } else {
                                $failed_delete_download_ids[] = $row->download_id;
                                $failed_delete_download_names[] = $row->download_name;
                            }
                        }
                        unset($donot_delete, $removeWebhook);
                    }
                }// endforeach;
                unset($apiHeader, $current_user_id, $FileSystem, $Github, $row);

                // check deleted, failed, result and set the error message.
                if (count($download_ids) === count($deleted_download_ids)) {
                    $output['form_result_class'] = 'notice-success';
                    $output['form_result_msg'] = __('Success! All selected items have been deleted.', 'rd-downloads');
                    if (!empty($failed_remove_githubwebhook)) {
                        $output['form_result_class'] = 'notice-warning';
                        $output['form_result_msg'] .= '<br>' . __('There are some webhook that is unable to remove, here is the result.', 'rd-downloads') . ' ' . implode(', ', $failed_remove_githubwebhook);
                    }
                } else {
                    $notfound_download_ids = array_diff($download_ids, $found_download_ids);

                    $output['form_result_class'] = 'notice-warning';
                    $output['form_result_msg'] = '<p><strong>' . __('Warning! There are some problem about delete the items, here are the results.', 'rd-downloads') . '</strong></p>' .
                        '<ul class="rd-downloads-ul">' .
                            (count($deleted_download_names) > 0 ? '<li><strong>' . _n('Deleted item', 'Deleted items', count($deleted_download_names), 'rd-downloads') . ':</strong> ' . implode(', ', $deleted_download_names) . '</li>' : '') .
                            (count($failed_delete_download_names) > 0 ? '<li><strong>' . _n('Failed to delete item', 'Failed to delete items', count($failed_delete_download_names), 'rd-downloads') . ':</strong> ' . implode(', ', $failed_delete_download_names) . '</li>' : '') .
                            (count($failed_remove_githubwebhook) > 0 ? '<li><strong>' . _n('Failed to remove webhook on GitHub', 'Failed to remove webhooks on GitHub', count($failed_remove_githubwebhook), 'rd-downloads') . ':</strong> ' . implode(', ', $failed_remove_githubwebhook) . '</li>' : '') .
                            (count($capability_limited_download_names) > 0 ? '<li><strong>' . _n('Capability limited item', 'Capability limited items', count($capability_limited_download_names), 'rd-downloads') . ':</strong> ' . implode(', ', $capability_limited_download_names) . '</li>' : '') .
                            (count($notfound_download_ids) > 0 ? '<li><strong>' .  _n('Mismatch ID', 'Mismatch IDs', count($notfound_download_ids), 'rd-downloads') . ':</strong> ' . implode(', ', $notfound_download_ids) . '</li>' : '') .
                        '</ul>';
                }

                // set additional result.
                $output['additionalResults'] = [
                    'found_download_ids' => $found_download_ids,
                    'capability_limited_download_ids' => $capability_limited_download_ids,
                    'deleted_download_ids' => $deleted_download_ids,
                    'failed_delete_download_ids' => $failed_delete_download_ids,
                    'notfound_download_ids' => (isset($notfound_download_ids) ? $notfound_download_ids : []),
                ];

                unset($capability_limited_download_ids, $capability_limited_download_names);
                unset($failed_delete_download_ids, $failed_delete_download_names);
                unset($failed_remove_githubwebhook);
                unset($found_download_ids, $notfound_download_ids);
                unset($deleted_download_ids, $deleted_download_names);
            } else {
                $responseStatus = 404;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('The selected items was not found.', 'rd-downloads');
            }
            unset($results);

            wp_send_json($output, $responseStatus);
        }// deleteDownloads


        /**
         * Perform get GitHub repository data and then update the file size, URL.
         *
         * This method will response json and end process.
         *
         * @global \wpdb $wpdb
         * @param array $download_ids The `download_id` values in one array. Example: `array(1, 2, 4, 5, 6);`
         */
        protected function githubUpdate(array $download_ids)
        {
            $responseStatus = 200;
            $output = [];

            // get the data from DB.
            global $wpdb;
            // use WHERE IN to search for *any* of the values. https://mariadb.com/kb/en/library/in/
            $sql = 'SELECT `download_id`, `user_id`, `download_name`, `download_type`, `download_github_name`, `download_url`, `download_options`
                FROM `' . $wpdb->prefix . 'rd_downloads`
                WHERE `download_id` IN (' . implode(', ', array_fill(0, count($download_ids), '%d')) . ')';// https://stackoverflow.com/a/10634225/128761
            $sql .= ' AND `download_type` = 1';
            $sql .= ' AND `download_github_name` IS NOT NULL';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    $sql,
                    $download_ids
                )
            );

            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                $output['debugSQL'] = $sql;// before executed sql statement (contain %s placeholder for prepare).
                $output['debugLastQuery'] = $wpdb->last_query;// executed sql statement.
                $output['download_ids'] = $download_ids;
            }
            unset($sql);

            if (count($results) > 0 && (is_array($results) || is_object($results))) {
                // if found the results.
                $current_user_id = get_current_user_id();

                $found_download_ids = [];
                $capability_limited_download_ids = [];
                $capability_limited_download_names = [];
                $updated_download_ids = [];
                $updated_download_names = [];
                $failed_update_download_ids = [];
                $failed_update_download_names = [];

                $Github = new \RdDownloads\App\Libraries\Github();
                $FileSystem = new \RdDownloads\App\Libraries\FileSystem();

                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    $output['debug_sql_lasterror'] = [];
                }

                foreach ($results as $row) {
                    $found_download_ids[] = $row->download_id;
                    if ($row->user_id != $current_user_id && !current_user_can('edit_others_posts')) {
                        // this user is trying to editing/updating others download and don't have capability to do it.
                        // this condition is unable to update the data.
                        $capability_limited_download_ids[] = $row->download_id;
                        $capability_limited_download_names[] = $row->download_name;
                    } else {
                        // this condition is able to update the data.
                        $download_options = maybe_unserialize($row->download_options);
                        $version_range = '';
                        if (
                            is_array($download_options) &&
                            array_key_exists('opt_download_version_range', $download_options) &&
                            array_key_exists('opt_download_version', $download_options)
                        ) {
                            if (
                                empty($download_options['opt_download_version_range']) &&
                                !empty($download_options['opt_download_version'])
                            ) {
                                $Semver = new \RdDownloads\App\Libraries\Semver();
                                $version_range = $Semver->getDefaultVersionConstraint($download_options['opt_download_version']);
                                unset($Semver);
                            } else {
                                $version_range = $download_options['opt_download_version_range'];
                            }
                        }

                        $githubResult = $Github->apiGetLatestRepositoryData($row->download_url, $version_range);
                        unset($version_range);

                        if ($githubResult !== false) {
                            // prepare update data.
                            $data = [];
                            $data['download_url'] = (isset($githubResult['url']) ? $githubResult['url'] : $row->download_url);
                            $data['download_github_name'] = (isset($githubResult['nameWithOwner']) ? $githubResult['nameWithOwner'] : $row->download_github_name);
                            $data['download_size'] = (isset($githubResult['size']) && $githubResult['size'] >= '0' ? $githubResult['size'] : '0');
                            $fileParts = $FileSystem->getFilePart($data['download_url']);
                            if (isset($fileParts['nameext'])) {
                                $data['download_file_name'] = $fileParts['nameext'];
                            }
                            unset($fileParts);
                            if (isset($githubResult['version'])) {
                                if (isset($download_options) && is_array($download_options)) {
                                    $download_options['opt_download_version'] = $githubResult['version'];
                                    $data['download_options'] = maybe_serialize($download_options);
                                }
                            }

                            $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
                            $updateResult = $RdDownloads->update($data, ['download_id' => $row->download_id]);
                            if ($updateResult !== false) {
                                $updated_download_ids[] = $row->download_id;
                                $updated_download_names[] = $row->download_name;
                                $Dll = new \RdDownloads\App\Models\RdDownloadLogs();
                                $Dll->writeLog('admin_update', [
                                    'download_id' => $row->download_id,
                                ]);
                                unset($Dll);
                            } else {
                                $failed_update_download_ids[] = $row->download_id;
                                $failed_update_download_names[] = $row->download_name;
                                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                                    $output['debug_sql_lasterror'][] = $wpdb->last_error;
                                }
                            }
                            unset($data);
                        } else {
                            $failed_update_download_ids[] = $row->download_id;
                            $failed_update_download_names[] = $row->download_name;
                        }
                        unset($download_options, $githubResult);
                    }
                }// endforeach;
                unset($current_user_id, $Github, $FileSystem, $row);

                // check updated, failed, result and set the error message.
                if (count($download_ids) === count($updated_download_ids)) {
                    $output['form_result_class'] = 'notice-success';
                    $output['form_result_msg'] = __('Success! All selected items have been updated.', 'rd-downloads');
                } else {
                    $notfound_download_ids = array_diff($download_ids, $found_download_ids);

                    $output['form_result_class'] = 'notice-warning';
                    $output['form_result_msg'] = '<p><strong>' . __('Warning! There are some problem about update the items, here are the results.', 'rd-downloads') . '</strong></p>' .
                        '<ul class="rd-downloads-ul">' .
                            (count($updated_download_names) > 0 ? '<li><strong>' . _n('Updated item', 'Updated items', count($updated_download_names), 'rd-downloads') . ':</strong> ' . implode(', ', $updated_download_names) . '</li>' : '') .
                            (count($failed_update_download_names) > 0 ? '<li><strong>' . _n('Failed to update item', 'Failed to update items', count($failed_update_download_names), 'rd-downloads') . ':</strong> ' . implode(', ', $failed_update_download_names) . '</li>' : '') .
                            (count($capability_limited_download_names) > 0 ? '<li><strong>' . _n('Capability limited item', 'Capability limited items', count($capability_limited_download_names), 'rd-downloads') . ':</strong> ' . implode(', ', $capability_limited_download_names) . '</li>' : '') .
                            (count($notfound_download_ids) > 0 ? '<li><strong>' .  _n('Mismatch ID', 'Mismatch IDs', count($notfound_download_ids), 'rd-downloads') . ':</strong> ' . implode(', ', $notfound_download_ids) . '</li>' : '') .
                        '</ul>';
                }

                // set additional result.
                $output['additionalResults'] = [
                    'found_download_ids' => $found_download_ids,
                    'capability_limited_download_ids' => $capability_limited_download_ids,
                    'updated_download_ids' => $updated_download_ids,
                    'failed_update_download_ids' => $failed_update_download_ids,
                    'notfound_download_ids' => (isset($notfound_download_ids) ? $notfound_download_ids : []),
                ];

                unset($capability_limited_download_ids, $capability_limited_download_names);
                unset($failed_update_download_ids, $failed_update_download_names);
                unset($found_download_ids, $notfound_download_ids);
                unset($updated_download_ids, $updated_download_names);
            } else {
                $responseStatus = 404;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('The selected items was not found or not matched download type.', 'rd-downloads');
            }
            unset($results);

            wp_send_json($output, $responseStatus);
        }// githubUpdate


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdDownloadsBulkActions', [$this, 'bulkActions']);
            }
        }// registerHooks


        /**
         * Perform get remote file data and then update the file size.
         *
         * This method will response json and end process.
         *
         * @global \wpdb $wpdb
         * @param array $download_ids The `download_id` values in one array. Example: `array(1, 2, 4, 5, 6);`
         */
        protected function remoteUpdate(array $download_ids)
        {
            $responseStatus = 200;
            $output = [];

            // get the data from DB.
            global $wpdb;
            // use WHERE IN to search for *any* of the values. https://mariadb.com/kb/en/library/in/
            $sql = 'SELECT `download_id`, `user_id`, `download_name`, `download_type`, `download_url`
                FROM `' . $wpdb->prefix . 'rd_downloads`
                WHERE `download_id` IN (' . implode(', ', array_fill(0, count($download_ids), '%d')) . ')';// https://stackoverflow.com/a/10634225/128761
            $sql .= ' AND `download_type` = 2';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    $sql,
                    $download_ids
                )
            );

            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                $output['debugSQL'] = $sql;// before executed sql statement (contain %s placeholder for prepare).
                $output['debugLastQuery'] = $wpdb->last_query;// executed sql statement.
                $output['download_ids'] = $download_ids;
            }
            unset($sql);

            if (count($results) > 0 && (is_array($results) || is_object($results))) {
                // if found the results.
                $current_user_id = get_current_user_id();

                $found_download_ids = [];
                $capability_limited_download_ids = [];
                $capability_limited_download_names = [];
                $updated_download_ids = [];
                $updated_download_names = [];
                $failed_update_download_ids = [];
                $failed_update_download_names = [];

                $Url = new \RdDownloads\App\Libraries\Url();

                foreach ($results as $row) {
                    $found_download_ids[] = $row->download_id;
                    if ($row->user_id != $current_user_id && !current_user_can('edit_others_posts')) {
                        // this user is trying to editing/updating others download and don't have capability to do it.
                        // this condition is unable to update the data.
                        $capability_limited_download_ids[] = $row->download_id;
                        $capability_limited_download_names[] = $row->download_name;
                    } else {
                        // this condition is able to update the data.
                        $remoteFileResult = $Url->getRemoteFileInfo($row->download_url);
                        if ($remoteFileResult !== false) {
                            // prepare update data.
                            $data = [];
                            $data['download_size'] = (isset($remoteFileResult['size']) && $remoteFileResult['size'] >= '0' ? $remoteFileResult['size'] : '0');
                            $data['download_update'] = current_time('mysql');
                            $data['download_update_gmt'] = current_time('mysql', true);

                            $updateResult = $wpdb->update($wpdb->prefix . 'rd_downloads', $data, ['download_id' => $row->download_id]);
                            if ($updateResult !== false) {
                                $updated_download_ids[] = $row->download_id;
                                $updated_download_names[] = $row->download_name;
                            } else {
                                $failed_update_download_ids[] = $row->download_id;
                                $failed_update_download_names[] = $row->download_name;
                            }
                            unset($data);
                        } else {
                            $failed_update_download_ids[] = $row->download_id;
                            $failed_update_download_names[] = $row->download_name;
                        }
                        unset($remoteFileResult);
                    }
                }// endforeach;
                unset($current_user_id, $row, $Url);

                // check updated, failed, result and set the error message.
                if (count($download_ids) === count($updated_download_ids)) {
                    $output['form_result_class'] = 'notice-success';
                    $output['form_result_msg'] = __('Success! All selected items have been updated.', 'rd-downloads');
                } else {
                    $notfound_download_ids = array_diff($download_ids, $found_download_ids);

                    $output['form_result_class'] = 'notice-warning';
                    $output['form_result_msg'] = '<p><strong>' . __('Warning! There are some problem about update the items, here are the results.', 'rd-downloads') . '</strong></p>' .
                        '<ul class="rd-downloads-ul">' .
                            (count($updated_download_names) > 0 ? '<li><strong>' . _n('Updated item', 'Updated items', count($updated_download_names), 'rd-downloads') . ':</strong> ' . implode(', ', $updated_download_names) . '</li>' : '') .
                            (count($failed_update_download_names) > 0 ? '<li><strong>' . _n('Failed to update item', 'Failed to update items', count($failed_update_download_names), 'rd-downloads') . ':</strong> ' . implode(', ', $failed_update_download_names) . '</li>' : '') .
                            (count($capability_limited_download_names) > 0 ? '<li><strong>' . _n('Capability limited item', 'Capability limited items', count($capability_limited_download_names), 'rd-downloads') . ':</strong> ' . implode(', ', $capability_limited_download_names) . '</li>' : '') .
                            (count($notfound_download_ids) > 0 ? '<li><strong>' .  _n('Mismatch ID', 'Mismatch IDs', count($notfound_download_ids), 'rd-downloads') . ':</strong> ' . implode(', ', $notfound_download_ids) . '</li>' : '') .
                        '</ul>';
                }

                // set additional result.
                $output['additionalResults'] = [
                    'found_download_ids' => $found_download_ids,
                    'capability_limited_download_ids' => $capability_limited_download_ids,
                    'updated_download_ids' => $updated_download_ids,
                    'failed_update_download_ids' => $failed_update_download_ids,
                    'notfound_download_ids' => (isset($notfound_download_ids) ? $notfound_download_ids : []),
                ];

                unset($capability_limited_download_ids, $capability_limited_download_names);
                unset($failed_update_download_ids, $failed_update_download_names);
                unset($found_download_ids, $notfound_download_ids);
                unset($updated_download_ids, $updated_download_names);
            } else {
                $responseStatus = 404;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('The selected items was not found or not matched download type.', 'rd-downloads');
            }
            unset($results);

            wp_send_json($output, $responseStatus);
        }// remoteUpdate


    }
}
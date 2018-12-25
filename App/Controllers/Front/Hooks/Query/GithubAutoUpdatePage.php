<?php
/**
 * GitHub auto update.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front\Hooks\Query;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Front\\Hooks\\Query\\GithubAutoUpdatePage')) {
    class GithubAutoUpdatePage implements \RdDownloads\App\Controllers\ControllerInterface
    {


        use \RdDownloads\App\AppTrait;


        /**
         * Accept GitHub webhook for auto update.
         */
        public function githubAutoUpdatePage()
        {
            if (get_query_var('pagename') !== 'rddownloads_github_autoupdate') {
                return false;
            }

            $Github = new \RdDownloads\App\Libraries\Github();
            if ($Github->isSettingToAutoUpdate() !== true) {
                return false;
            }

            $phpinput = file_get_contents('php://input');
            $Github->webhook(getallheaders(), $phpinput);

            $this->getOptions();
            global $rd_downloads_options;

            if ($Github->validateGitHubWebhook() === true) {
                // if validation passed.

                $headerEvent = $Github->webhookGetHeaderEvent();

                // check header event trigger.
                if ($headerEvent === 'ping') {
                    // if pinging.
                    // check for ping.
                    $this->githubPingCheck($Github);
                } elseif ($headerEvent === 'push' || $headerEvent === 'release') {
                    // if push event trigger.
                    $payloadObject = json_decode($phpinput);

                    if ($Github->webhookIsCommit() === true) {
                        // if this event is commit.
                        if (isset($rd_downloads_options['rdd_github_auto_update']) && $rd_downloads_options['rdd_github_auto_update'] == 'release+commit') {
                            // if global setting is allow to update release+commit.
                            $this->githubUpdateData($Github, $payloadObject);
                        } else {
                            // if global setting is NOT allow to update release+commit or just allow commit only.
                            // don't do it.
                            status_header(200);
                            exit();
                        }
                    } elseif ($Github->webhookIsTagging('created') || $Github->webhookIsTagging('deleted')) {
                        // if this event is tagging.
                        if (
                            isset($rd_downloads_options['rdd_github_auto_update']) && 
                            (
                                $rd_downloads_options['rdd_github_auto_update'] == 'release+commit' ||
                                $rd_downloads_options['rdd_github_auto_update'] == 'release'
                            )
                        ) {
                            // if global setting is allow to update release or release+commit.
                            $this->githubUpdateData($Github, $payloadObject);
                        } else {
                            // if global setting is NOT allow to update release+commit or just allow commit only.
                            // don't do it.
                            status_header(200);
                            exit();
                        }
                    }// endif; check webhook event. (is commit, tag created, tag deleted).

                    unset($payloadObject);
                    // inside if push even trigger.
                }// endif; check header event trigger.
            } else {
                // if validation is not passed.
                unset($Github, $phpinput);
                status_header(403);
            }

            exit();// required to display just thispage, otherwise the normal WordPress page will be render.
        }// githubAutoUpdatePage


        /**
         * GitHub ping check.
         * 
         * The process will be end here.
         * 
         * @param \RdDownloads\App\Libraries\Github $Github The GitHub object from main method.
         */
        private function githubPingCheck(\RdDownloads\App\Libraries\Github $Github)
        {
            $acceptPingResult = $Github->webhookPingCheckConfig();

            if ($acceptPingResult === true) {
                status_header(200);
                exit();
            } elseif ($acceptPingResult === false) {
                status_header(400);
                wp_send_json(['config_error' => 'required: event = push, content-type = application/json'], 400);
            }

            // come to this means not pinging but the header in caller method was ping.
            // send status just 202 (Accepted).
            status_header(202);
            exit();
        }// githubPingCheck


        /**
         * Get latest version data matched on version range.
         * 
         * This method was called from `githubUpdateData()` method.
         * 
         * @param array $latestData The latest data from `\RdDownloads\App\Libraries\GitHub::getLatestRepositoryData()` method.
         * @param string $version_range The version range.
         * @param array $download_options Download options in DB.
         * @return array Return the associate array where table field is key.
         */
        private function githubGetLatestVersionRange(array $latestData, $version_range, array $download_options)
        {
            $output = [];

            // loop each releases data from latest data fetched from GitHub.
            // and get the latest or matched version range.
            foreach ($latestData as $item) {
                if (empty($version_range)) {
                    // if version range was not set.
                    // get the latest.
                    if (isset($item['nameWithOwner'])) {
                        $output['download_github_name'] = $item['nameWithOwner'];
                    }
                    if (isset($item['url'])) {
                        $output['download_url'] = $item['url'];
                    }
                    if (isset($item['size'])) {
                        $output['download_size'] = $item['size'];
                    }
                    if (isset($item['version'])) {
                        $download_options['opt_download_version'] = $item['version'];
                    }
                    $output['download_options'] = maybe_serialize($download_options);
                    break;
                } else {
                    // if version range was set
                    if (isset($item['version']) && \RdDownloads\Composer\Semver\Semver::satisfies($item['version'], $version_range)) {
                        // if matched version range.
                        if (isset($item['nameWithOwner'])) {
                            $output['download_github_name'] = $item['nameWithOwner'];
                        }
                        if (isset($item['url'])) {
                            $output['download_url'] = $item['url'];
                        }
                        if (isset($item['size'])) {
                            $output['download_size'] = $item['size'];
                        }
                        if (isset($item['version'])) {
                            $download_options['opt_download_version'] = $item['version'];
                        }
                        $output['download_options'] = maybe_serialize($download_options);
                        break;
                    }
                }// endif;
            }// endforeach; $latestData
            unset($item);

            return $output;
        }// githubGetLatestVersionRange


        /**
         * Get latest repository data and check if there is something change then update.
         * 
         * @todo [rd-downloads] make auto update on multiple rows in db in case that the same repository is in many download rows.
         * @global \wpdb $wpdb
         * @param \RdDownloads\App\Libraries\Github $Github The GitHub object from main method.
         * @param object $payloadObject The payload object from "php://input"
         */
        private function githubUpdateData(\RdDownloads\App\Libraries\Github $Github, $payloadObject)
        {
            \RdDownloads\App\Libraries\Logger::staticDebugLog($payloadObject, 'github-payload-' . current_time('Ymd-Hi'));

            if (isset($payloadObject->repository->url) && isset($payloadObject->repository->full_name)) {
                // if payload object contain url, owner name with repository name (owner/reponame).
                // get the data in db to check.
                $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
                $options = [];
                $options['download_github_name'] = $payloadObject->repository->full_name;
                $results = $RdDownloads->listItems($options);
                unset($options);

                if (isset($results['results']) && is_array($results['results']) && !empty($results['results'])) {
                    // if found data in db.
                    // get latest data from GitHub.
                    $latestData = $Github->getLatestRepositoryData($payloadObject->repository->url, []);

                    if (is_array($latestData) && isset($latestData[0]) && is_array($latestData[0])) {
                        // if fetched latest data form GitHub success.
                        $updatedDb = 0;
                        $updatedErrors = false;
                        foreach ($results['results'] as $downloadRow) {
                            $download_options = maybe_unserialize($downloadRow->download_options);
                            $version_range = '';
                            if (
                                is_array($download_options) && 
                                array_key_exists('opt_download_version_range', $download_options) &&
                                array_key_exists('opt_download_version', $download_options)
                            ) {
                                // if there are options for version & version range.
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
                            }// endif; $download_options

                            $data = [];
                            $where = [];
                            $where['download_id'] = $downloadRow->download_id;

                            $data = $this->githubGetLatestVersionRange($latestData, $version_range, $download_options);
                            unset($download_options);

                            if (is_array($data) && !empty($data) && is_array($where) && !empty($where)) {
                                $updateResult = $RdDownloads->update($data, $where);
                                unset($data, $where);

                                if ($updateResult !== false) {
                                    // if update success.
                                    $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
                                    $RdDownloadLogs->writeLog('github_autoupdate', [
                                        'download_id' => $downloadRow->download_id,
                                    ]);
                                    unset($downloadRow, $RdDownloadLogs);

                                    $updatedDb++;
                                } else {
                                    $updatedErrors = true;
                                }
                                unset($updateResult);
                            }// endif;

                        }// endforeach; $results['results'] 
                        unset($data, $downloadRow, $where);

                        unset($latestData, $RdDownloads);

                        if ($updatedErrors === true) {
                            global $wpdb;
                            status_header(500);
                            wp_send_json([
                                'error' => $wpdb->last_error,
                            ], 500);
                        } elseif ($updatedDb >= 0) {
                            status_header(200);
                            wp_send_json([
                                'updated' => true,
                                'updatedTotal' => $updatedDb,
                            ], 200);
                        }
                    } else {
                        // if failed to fetched latest data from GitHub.
                        // no need to do anything here.
                        unset($RdDownloads, $results);

                        status_header(200);
                        wp_send_json([
                            'updated' => false,
                            'failedGetLatestData' => true,
                            'latestData' => (defined('WP_DEBUG') && WP_DEBUG === true ? $latestData : ''),
                        ], 200);
                    }// endif; $latestData
                } else {
                    // if not found any data in db.
                    // no need to do anything here.
                    unset($RdDownloads, $results);

                    status_header(200);
                    wp_send_json([
                        'updated' => false,
                        'notFoundInDb' => true,
                    ], 200);
                }// endif; $results['results']

                unset($RdDownloads, $results);
            }// endif; payload contain required objects.

            // come to this means, the payload does not contain required objects.
            status_header(400);
            exit();
        }// githubUpdateData


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (!is_admin()) {
                add_action('template_redirect', [$this, 'githubAutoUpdatePage']);
            }
        }// registerHooks


    }
}
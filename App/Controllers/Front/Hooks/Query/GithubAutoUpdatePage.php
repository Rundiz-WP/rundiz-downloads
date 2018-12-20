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
         * Get latest repository data and check if there is something change then update.
         * 
         * @global \wpdb $wpdb
         * @param \RdDownloads\App\Libraries\Github $Github The GitHub object from main method.
         * @param object $payloadObject The payload object from "php://input"
         */
        private function githubUpdateData(\RdDownloads\App\Libraries\Github $Github, $payloadObject)
        {
            if (isset($payloadObject->repository->url) && isset($payloadObject->repository->full_name)) {
                // if payload object contain url, owner name with repository name (owner/reponame).
                // get latest data.
                $latestData = $Github->getLatestRepositoryData($payloadObject->repository->url);

                if ($latestData !== false && is_array($latestData) && isset($latestData['url'])) {
                    // if fetched latest data success.
                    // get the data in db to check.
                    $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
                    $options = [];
                    $options['download_github_name'] = $payloadObject->repository->full_name;
                    $downloadRow = $RdDownloads->get($options);
                    unset($options);

                    if (is_object($downloadRow) && $downloadRow->download_url != $latestData['url']) {
                        // if found data in db and url is not same.
                        // the url in db is not same to latest maybe it is newer.
                        // update to db.
                        $data['download_url'] = $latestData['url'];
                        if (isset($latestData['size'])) {
                            $data['download_size'] = $latestData['size'];
                        }
                        unset($latestData);

                        $where['download_id'] = $downloadRow->download_id;

                        $updateResult = $RdDownloads->update($data, $where);
                        unset($data, $RdDownloads, $where);

                        if ($updateResult === false) {
                            // if failed to update.
                            unset($downloadRow);

                            status_header(500);

                            global $wpdb;
                            error_log($wpdb->last_error);
                            exit();
                        } else {
                            // if update success.
                            $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
                            $RdDownloadLogs->writeLog('github_autoupdate', [
                                'download_id' => $downloadRow->download_id,
                            ]);
                            unset($downloadRow, $RdDownloadLogs);

                            status_header(200);
                            wp_send_json([
                                'updated' => true,
                                'updateResult' => $updateResult,
                            ], 200);
                        }
                    }// endif; found in db and url is not same.

                    // come to this means not found data in db or url is same.
                    // no need to do anything here. end the process.
                    unset($downloadRow, $latestData, $RdDownloads);
                    status_header(200);
                    exit();
                }// endif; fetched latest data success.

                unset($latestData);
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
<?php
/**
 * GitHub auto update main page.
 *
 * This file will be working on GitHub send push event to this site.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front\Hooks\Query\GithubAutoUpdatePage;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Front\\Hooks\\Query\\GithubAutoUpdatePage\\GithubAutoUpdatePage')) {
    class GithubAutoUpdatePage extends \RdDownloads\App\Controllers\Front\ControllerBased
    {


        /**
         * @var \RdDownloads\App\Libraries\Github
         */
        protected $Github;


        /**
         * @var object
         */
        protected $payloadObject;


        /**
         * @var \RdDownloads\App\Models\RdDownloads The `RdDownloads` model.
         */
        protected $RdDownloads;


        use \RdDownloads\App\AppTrait;


        public function __construct()
        {
            $this->getOptions();

            $this->Github = new \RdDownloads\App\Libraries\Github();
            $this->payloadObject = null;
            $this->RdDownloads = null;
        }// __construct


        /**
         * GitHub send webhook and go to here.
         */
        public function pageIndex()
        {
            // set page title.
            $this->setTitle(__('Rundiz Downloads', 'rd-downloads'));

            $phpinput = file_get_contents('php://input');
            $this->Github->webhook(getallheaders(), $phpinput);

            if ($this->Github->validateGitHubWebhook() === true) {
                // if validation passed.
                $headerEvent = $this->Github->webhookGetHeaderEvent();

                // check header event trigger.
                if ($headerEvent === 'ping') {
                    // if pinging.
                    // check for ping.
                    $this->subGithubPingCheck();
                } elseif ($headerEvent === 'push' || $headerEvent === 'release') {
                    // if push event trigger.
                    $this->payloadObject = json_decode($phpinput);
                    unset($headerEvent, $phpinput);
                    // check for commit or release event and then update.
                    $this->subGithubPush();
                }// endif; check header event trigger.
                unset($headerEvent);
            } else {
                // if validation is not passed.
                unset($phpinput);
                status_header(403);
                wp_send_json(['result' => 'invalid webhook'], 403);
            }

            unset($phpinput);
        }// pageIndex


        /**
         * GitHub ping check.
         *
         * The process will be end here.
         */
        protected function subGithubPingCheck()
        {
            $acceptPingResult = $this->Github->webhookPingCheckConfig();

            if ($acceptPingResult === true) {
                wp_send_json(['ping' => 'pong', 'pingDate' => current_time('mysql', true)], 200);
                exit();
            } elseif ($acceptPingResult === false) {
                wp_send_json(['config_error' => 'required: event = push, content-type = application/json'], 400);
            }

            // come to this means not pinging but the header in caller method was ping.
            // send status just 202 (Accepted).
            status_header(202);
            exit();
        }// githubPingCheck


        /**
         * GitHub push events. Check that is it commit or release event and then update.
         *
         * The process will be end here.
         *
         * @global array $rd_downloads_options
         */
        protected function subGithubPush()
        {
            global $rd_downloads_options;

            $output = [];

            if ($this->Github->webhookIsCommit() === true) {
                // if this event is commit.
                if (isset($rd_downloads_options['rdd_github_auto_update']) && $rd_downloads_options['rdd_github_auto_update'] == 'release+commit') {
                    // if global setting is allow to update release+commit.
                    $this->subGithubPushUpdateData();
                } else {
                    // if global setting is NOT allow to update release+commit. it just allow release only.
                    $output['updated'] = false;
                    $output['webhook_event'] = 'commit';
                    $output['rdd_autoupdate_config'] = $rd_downloads_options['rdd_github_auto_update'];
                }// endif;
            } elseif ($this->Github->webhookIsTagging('created') || $this->Github->webhookIsTagging('deleted')) {
                // if this event is tagging.
                if (
                    isset($rd_downloads_options['rdd_github_auto_update']) &&
                    (
                        $rd_downloads_options['rdd_github_auto_update'] == 'release+commit' ||
                        $rd_downloads_options['rdd_github_auto_update'] == 'release'
                    )
                ) {
                    // if global setting is allow to update release or release+commit.
                    $this->subGithubPushUpdateData();
                } else {
                    // if global setting is NOT allow to update release+commit, release only. there is something terrible wrong in config.
                    $output['updated'] = false;
                    $output['webhook_event'] = 'release';
                    $output['rdd_autoupdate_config'] = $rd_downloads_options['rdd_github_auto_update'];
                }// endif;
            }// endif; check webhook event. (is commit, tag created, tag deleted).

            wp_send_json($output, 200);
        }// subGithubPush


        /**
         * Do update data into DB.
         *
         * This method was called from `subGithubPushUpdateData()` method.
         *
         * @global \wpdb $wpdb
         * @param array $results The query results from DB where it contain matched `download_github_name` field.
         * @param array $latestData The latest data from `\RdDownloads\App\Libraries\GitHub::apiGetLatestRepositoryData()` method.
         * @return array Return array with "responseStatus" and other keys.
         */
        private function subGithubPushDoUpdateData(array $results, array $latestData)
        {
            $updatedDb = 0;
            $updatedErrors = false;
            $output = [];

            foreach ($results as $downloadRow) {
                $download_options = maybe_unserialize($downloadRow->download_options);
                $version_range = $this->subGithubPushGetVersionRange($download_options);

                $data = [];
                $where = [];
                $where['download_id'] = $downloadRow->download_id;

                $data = $this->subGithubPushGetLatestVersionRange($latestData, $version_range, $download_options);
                unset($download_options);

                if (is_array($data) && !empty($data) && is_array($where) && !empty($where)) {
                    // if data is set and ready for update.
                    $updateResult = $this->RdDownloads->update($data, $where);
                    unset($data, $where);

                    if ($updateResult !== false) {
                        // if update success.
                        $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
                        $RdDownloadLogs->writeLog('github_autoupdate', [
                            'download_id' => $downloadRow->download_id,
                        ]);
                        unset($RdDownloadLogs);

                        $updatedDb++;
                    } else {
                        $updatedErrors = true;
                    }
                    unset($updateResult);
                }// endif;

                unset($data, $where, $version_range);
            }// endforeach; $results
            unset($downloadRow);

            if ($updatedErrors === true) {
                global $wpdb;
                $output['responseStatus'] = 500;
                $output['error'] = $wpdb->last_error;
            } elseif ($updatedDb >= 0) {
                $output['responseStatus'] = 200;
                $output['updated'] = true;
                $output['updatedTotal'] = $updatedDb;
            }

            unset($updatedDb, $updatedErrors);
            return $output;
        }// subGithubPushDoUpdateData


        /**
         * Get latest version data matched on version range.
         *
         * This method was called from `subGithubPushDoUpdateData()` method.
         *
         * @param array $latestData The latest data from `\RdDownloads\App\Libraries\GitHub::apiGetLatestRepositoryData()` method.
         * @param string $version_range The version range.
         * @param array $download_options Download options in DB.
         * @return array Return the associate array where table field is key.
         */
        private function subGithubPushGetLatestVersionRange(array $latestData, $version_range, array $download_options)
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
                        if (
                            isset($download_options['opt_download_version']) &&
                            \RdDownloads\Composer\Semver\Semver::satisfies($item['version'], '=' . $download_options['opt_download_version'])
                        ) {
                            // if got version from GitHub as same as the version on DB.
                            // skip it.
                            break;
                        }

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
        }// subGithubPushGetLatestVersionRange


        /**
         * Get version range from `download_options` field.
         *
         * @param string|array $download_options The data from `download_options` field. If string given, it will be unserialize, if array given it was already unserialized.
         * @return string Return version range.
         */
        private function subGithubPushGetVersionRange($download_options)
        {
            if (is_string($download_options)) {
                $download_options = maybe_unserialize($download_options);
            }
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

            return $version_range;
        }// subGithubPushGetVersionRange


        /**
         * Get latest repository data and check if there is something change then update.
         *
         * This method was called from `subGithubPush()` method.
         *
         * The process will be end here.
         */
        private function subGithubPushUpdateData()
        {
            $responseStatus = 200;
            $output = [];

            \RdDownloads\App\Libraries\Logger::staticDebugLog($this->payloadObject, 'github-payload-' . current_time('Ymd-Hi'));

            if (isset($this->payloadObject->repository->url) && isset($this->payloadObject->repository->full_name)) {
                // if payload object contain url, owner name with repository name (owner/reponame).
                // get the data in db to check.
                $this->RdDownloads = new \RdDownloads\App\Models\RdDownloads();
                $options = [];
                $options['download_github_name'] = $this->payloadObject->repository->full_name;
                $results = $this->RdDownloads->listItems($options);
                unset($options);

                if (isset($results['results']) && is_array($results['results']) && !empty($results['results'])) {
                    // if found data in db.
                    // get valid user and his key.
                    $secretKeyArray = $this->Github->getWebhookValidSecretKey();
                    if (is_array($secretKeyArray)) {
                        $user_id = key($secretKeyArray);
                    } else {
                        $user_id = '';
                    }
                    unset($secretKeyArray);

                    // get latest data from GitHub.
                    $latestData = $this->Github->apiGetLatestRepositoryData($this->payloadObject->repository->url, [], $user_id);
                    unset($user_id);

                    if (is_array($latestData) && isset($latestData[0]) && is_array($latestData[0])) {
                        // if fetched latest data form GitHub success.
                        // call to do update data. the version range compare will be in this method.
                        $updateResult = $this->subGithubPushDoUpdateData($results['results'], $latestData);
                        if (isset($updateResult['responseStatus'])) {
                            $responseStatus = $updateResult['responseStatus'];
                            unset($updateResult['responseStatus']);
                        }
                        $output = $output + $updateResult;
                    } else {
                        // if failed to fetched latest data from GitHub.
                        // no need to do anything here.
                        $output['updated'] = false;
                        $output['failedGetLatestData'] = true;
                        if (defined('WP_DEBUG') && WP_DEBUG === true) {
                            $output['debug_latestData'] = $latestData;
                        }
                    }// endif; $latestData

                    unset($latestData);
                } else {
                    // if not found any data in db.
                    // no need to do anything here.
                    $output['updated'] = false;
                    $output['notFoundInDb'] = true;
                }// endif; $results['results']

                unset($results);
                $this->RdDownloads = null;
            } else {
                // if payload does not contain required objects.
                $output['error'] = 'payload does not contain required objects.';
                $responseStatus = 400;
            }// endif; payload contain required objects.

            // response and end process.
            wp_send_json($output, $responseStatus);
        }// subGithubPushUpdateData


    }
}
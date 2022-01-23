<?php
/**
 * Front-end download process page.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front\Hooks\Query\DownloadPage;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Front\\Hooks\\Query\\DownloadPage\\RdDownloadsPage')) {
    /**
     * Process the download.
     *
     * This class was called from `App\Controllers\Front\Hooks\Query\DownloadPage` class -> `goToRdDownloadsPage()` method.
     */
    class RdDownloadsPage extends \RdDownloads\App\Controllers\Front\ControllerBased
    {


        use \RdDownloads\App\AppTrait;


        /**
         * @var \RdDownloads\App\Libraries\Loader The loader class.
         */
        protected $Loader;


        public function __construct()
        {
            $this->Loader = new \RdDownloads\App\Libraries\Loader();

            $this->getOptions();

            if (session_id() == '') {
                // if no session ID.
                // start the session.
                session_start();
            }
        }// __construct
    

        /**
         * Class destructor.
         * 
         * @since 1.0.5
         */
        public function __destruct()
        {
            session_write_close();
        }// __destruct


        /**
         * Do force download.
         *
         * This method contain `exit()` function, after call to this method the process will be stopped.
         *
         * @param object $downloadRow The download object get from `$wpdb->query()` method.
         * @param string $downloadFullPath The file full path.
         */
        private function doForceDownload($downloadRow, $downloadFullPath)
        {
            // force download code even memory was limited.
            // @link http://php.net/manual/en/function.readfile.php Use readfile() for send download file content. This function itself can read very large file.
            // @link https://stackoverflow.com/questions/7263923/how-to-force-file-download-with-php/ Force download examples.
            // @link https://stackoverflow.com/a/9182133/128761 Prevent ob_flush() error ( ob_flush(): failed to flush buffer. No buffer to flush ).
            // @link https://stackoverflow.com/a/32092523/128761 Correct the file's mime type.

            $Finfo = new \finfo();

            // These headers will force download on browser,
            // and set the custom file name for the download, respectively.
            header('Content-Type: ' . $Finfo->file($downloadFullPath, FILEINFO_MIME_TYPE));
            header('Content-Disposition: attachment; filename="' . $downloadRow->download_file_name . '"');
            header('Content-Length: ' . filesize($downloadFullPath));

            // No cache headers
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');

            unset($Finfo);

            // flush and turn off all the output buffering that were left.
            for ($i = 0; $i < ob_get_level(); $i++) {
                ob_end_flush();
            }

            // read file contents and send output to browser.
            $readfile = @readfile($downloadFullPath);

            if ($readfile === false) {
                $error = error_get_last();
                if (isset($error['message']) && is_scalar($error['message'])) {
                    error_log($error['message']);
                }
                unset($error);
            }
            unset($readfile);

            // done.
            exit();
        }// doForceDownload


        /**
         * User clicked on download button and go to here.
         *
         * These are process steps.
         *
         * Check that the setting was set to use antibot or not.<br>
         * Check that the user agent was blocked (banned) or not. (see `subCheckBannedUA()`)<br>
         * Check that `download_id` exists or not. (see `subGetDownloadData()`)<br>
         * Check that downloading file is local or not. (see `subGetDownloadData()`)<br>
         * Increase download count.<br>
         *  - If remote or GitHub then redirect to start download.<br>
         *  - If local then check that setting to force download (global and individual) or not. Start download after force download check.
         * 
         * This method will be echo out, or response to the browser including headers.
         *
         * @global array $rd_downloads_options
         * @param int The `download_id` to match in DB.
         */
        public function pageIndex($download_id)
        {
            if (!is_numeric($download_id) || $download_id <= 0) {
                // if download id is something wrong.
                // don't waste your time for this.
                return ;
            }

            global $rd_downloads_options;

            // set page title.
            $this->setTitle(__('Rundiz Downloads', 'rd-downloads'));

            // check for banned user agent.
            $result = $this->subCheckBannedUA($download_id);
            if (isset($result) && $result === true) {
                // if banned.
                // stop process here.
                unset($result);
                return ;
            }
            unset($result);

            if (isset($rd_downloads_options['rdd_use_antibotfield']) && !empty($rd_downloads_options['rdd_use_antibotfield'])) {
                // if setting was set to use anti bot form field.
                // do a filter hook to allow custom antibot.
                $useCustomAntibot = apply_filters('rddownloads_use_custom_antibot', false);

                if ($useCustomAntibot === true) {
                    // if there is filter hook to use custom antibot.
                    // do a filter hook to display anti bot page. while displaying anti bot page, return `false` only.
                    // when validate the anti bot form, return `true` on success and `false` on failure.
                    $stepAntibot = apply_filters('rddownloads_use_custom_antibot_result', false, $download_id);
                    if (!is_bool($stepAntibot)) {
                        $stepAntibot = false;
                    }
                } else {
                    $stepAntibot = $this->subUseAntibot($download_id);
                }

                unset($useCustomAntibot);
            } else {
                // if setting was not set to use antibot.
                $stepAntibot = true;
            }

            if (isset($stepAntibot) && $stepAntibot === true) {
                // if anti bot validated pass.
                // check download exists and start download is the last step.
                $this->subGetDownloadData($download_id);
            }
            unset($stepAntibot);
        }// pageIndex


        /**
         * Sub page check banned User Agent.
         *
         * @global array $rd_downloads_options
         * @param int $download_id The download_id field.
         * @return bool Return `true` if BANNED and displaying banned page.<br>
         *      Return `false` if not banned but not display anything.
         */
        protected function subCheckBannedUA($download_id)
        {
            global $rd_downloads_options;

            if (isset($rd_downloads_options['rdd_block_ua']) && !empty($rd_downloads_options['rdd_block_ua'])) {
                $currentUserAgent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_UNSAFE_RAW);
                $bannedUserAgents = explode("\n", str_replace(["\r\n", "\r", "\n"], "\n", $rd_downloads_options['rdd_block_ua']));

                if (is_array($bannedUserAgents)) {
                    foreach ($bannedUserAgents as $bannedUserAgent) {
                        if (stripos($currentUserAgent, $bannedUserAgent) !== false) {
                            // if user agent has been banned.
                            // write download log.
                            $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
                            $data = [];
                            $data['download_id'] = $download_id;
                            $RdDownloadLogs->writeLog('user_dl_banned', $data);
                            unset($data, $RdDownloadLogs);

                            status_header(403);
                            $output['banned'] = true;
                            $output['currentUserAgent'] = $currentUserAgent;
                            $output['matchBannedUserAgent'] = $bannedUserAgent;
                            $this->Loader->loadTemplate('RdDownloadsPage/subCheckBannedUA_v', $output);
                            return true;
                        }
                    }// endforeach;
                    unset($bannedUserAgent);
                }
                unset($bannedUserAgents, $currentUserAgent);
                return false;
            } else {
                // no banned user agent.
                return false;
            }
        }// subCheckBannedUA


        /**
         * Display download not found page and then return `false`.
         * 
         * @param int $download_id The `download_id` that matched in DB.
         * @return false Return `false`.
         */
        protected function subDownloadNotFound($download_id)
        {
            $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();

            // write download log.
            $data = [];
            $data['download_id'] = $download_id;
            $RdDownloadLogs->writeLog('user_dl_error', $data);
            unset($data, $RdDownloadLogs);

            status_header(404);
            $this->Loader->loadTemplate('RdDownloadsPage/subDownloadNotFound_v', ['download_not_found' => true]);

            return false;
        }// subDownloadNotFound


        /**
         * Sub page check for download item exists and start download.
         *
         * @global array $rd_downloads_options
         * @param int $download_id The `download_id` that matched in DB.
         * @return bool Return false for failure. If success then it will process the download here and exit.
         */
        protected function subGetDownloadData($download_id)
        {
            $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
            $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
            $downloadRow = $RdDownloads->get(['download_id' => $download_id]);

            if (empty($downloadRow) || is_null($downloadRow)) {
                // if not found.
                return $this->subDownloadNotFound($download_id);
            } else {
                // if found download item.
                // write download log.
                $data = [];
                $data['download_id'] = $download_id;
                $RdDownloadLogs->writeLog('user_dl_success', $data);
                unset($data, $RdDownloadLogs);

                // increase download count for now.
                $RdDownloads->increaseDownloadCount($download_id);
                unset($RdDownloads);

                if ($downloadRow->download_type !== '0') {
                    // if download type is NOT local.
                    // it is not possible or not good to use force download. just redirect.
                    // not set status header here because it is already in redirect function.
                    wp_redirect($downloadRow->download_url);
                    exit();
                } else {
                    // if download type is local.
                    // check for global setting is force download or not.
                    global $rd_downloads_options;
                    if (isset($rd_downloads_options['rdd_force_download']) && $rd_downloads_options['rdd_force_download'] == '1') {
                        // if "global setting" is using force download.
                        $forceDownload = true;
                    }

                    if (!isset($forceDownload) || (isset($forceDownload) && $forceDownload === false)) {
                        // if "global setting" was not set or means set to NOT force download.
                        // check for "per download setting" is force download or not.
                        $download_options = maybe_unserialize($downloadRow->download_options);
                        if (is_array($download_options) && isset($download_options['opt_force_download']) && $download_options['opt_force_download'] == '1') {
                            // if per download setting is using force download.
                            $forceDownload = true;
                        } else {
                            // otherwise per download setting can be redirect, use default.
                            // these other options than force download are same as redirect except that global setting is set to force download which will not go into this condition.
                        }
                        unset($download_options);
                    }

                    if (!isset($forceDownload) || (isset($forceDownload) && $forceDownload !== true)) {
                        // if not force download means redirect. do it.
                        wp_redirect($downloadRow->download_url);
                        exit();
                    }
                    unset($forceDownload);

                    // the code below is mostly about force download the file.
                    // force downloads ------------------------------------------------------------------------
                    $wp_upload_dir = wp_upload_dir();
                    if (isset($wp_upload_dir['basedir'])) {
                        // if can get wp upload dir.
                        $downloadFullPath = trailingslashit($wp_upload_dir['basedir']) . $downloadRow->download_related_path;
                        if (!is_file($downloadFullPath)) {
                            // if file is not exists.
                            $redirectInstead = true;
                        }
                    } else {
                        // if cannot get wp upload dir.
                        $redirectInstead = true;
                    }
                    unset($wp_upload_dir);

                    if (isset($redirectInstead) && $redirectInstead === true) {
                        // if there is something change. don't force download the file, redirect to it.
                        wp_redirect($downloadRow->download_url);
                        exit();
                    }
                    unset($redirectInstead);

                    status_header(200);

                    $this->doForceDownload($downloadRow, $downloadFullPath);
                    // force downloads ------------------------------------------------------------------------
                }// endif; download_type
            }// endif; download not found
        }// subGetDownloadData


        /**
         * Display antibot form field including validate the submitted form.
         * 
         * @param int $download_id
         * @return bool Return `true` if validated anti bot succeeded but not display anything.<br>
         *      Return `false` and display anti bot form page if form is not validated or validated but failed.
         */
        protected function subUseAntibot($download_id)
        {
            session_start();
            $cookieName = 'rddownloads_antibotcookietest';
            $output = [];

            // cookie test.
            $validatedCookieTest = false;
            if (!isset($_COOKIE[$cookieName]) || 'true' !== $_COOKIE[$cookieName]) {
                if (isset($_GET['redir-set-cookie'])) {
                    // if redirected but still not found cookie.
                    // just display banned message.
                    status_header(400);
                    $output['disableAntibotForm'] = true;
                    $output['form_result'] = 'error';
                    $output['form_result_msg'] = __('You are not authorized to download the file. Failed to set required cookie.', 'rd-downloads');
                } else {
                    // if not redirected.
                    // set cookie and redirect to page with ?redir-set-cookie=1.
                    \RdDownloads\App\Libraries\Cookies::setCookie($cookieName, 'true', time()+60*60*24*1);
                    wp_safe_redirect(add_query_arg(['redir-set-cookie' => 1]));
                    exit();
                }
            } else {
                $validatedCookieTest = true;
            }// endif;
            // end cookie test.

            if (true === $validatedCookieTest) {
                // if validated cookie test.
                // retrieve download data to show.
                $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
                $downloadRow = $RdDownloads->get(['download_id' => $download_id]);
                if (empty($downloadRow) || is_null($downloadRow)) {
                    // if not found.
                    return $this->subDownloadNotFound($download_id);
                } else {
                    $output['downloadRow'] = $downloadRow;
                }
                unset($downloadRow, $RdDownloads);
                // end retrieve download data to show.

                $requestMethod = (isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get');
                if ('get' === $requestMethod) {
                    // if method GET, displaying antibot form field.
                    $AntiBot = new \RdDownloads\App\Libraries\AntiBot();
                    $output['honeypotName'] = $AntiBot->setAndGetHoneypotName();
                    unset($AntiBot);
                } elseif ('post' === $requestMethod) {
                    // if method POST, process form submitted.
                    $honeypotName = \RdDownloads\App\Libraries\AntiBot::staticGetHoneypotName();
                    $validatedHoneypot = false;
                    if (!isset($_POST[$honeypotName]) || !empty($_POST[$honeypotName])) {
                        // if honeypot name is not in the form or it is in but not empty (bot filled).
                        status_header(400);
                        $output['disableAntibotForm'] = true;
                        $output['form_result'] = 'error';
                        $output['form_result_msg'] = __('You are not authorized to download the file. Failed to validate human.', 'rd-downloads');

                        $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
                        $RdDownloadLogs->writeLog('user_dl_antbotfailed', ['download_id' => $download_id]);
                        unset($RdDownloadLogs);
                    } elseif (isset($_POST[$honeypotName]) && empty($_POST[$honeypotName])) {
                        // if honeypot name is in the form and empty. correct!
                        $AntiBot = new \RdDownloads\App\Libraries\AntiBot();
                        $AntiBot->unsetHoneypotName();
                        unset($AntiBot);
                        $validatedHoneypot = true;
                    }
                    unset($honeypotName);

                    if (isset($validatedHoneypot) && true === $validatedHoneypot) {
                        // if validated honeypot.
                        $iamhuman = filter_input(INPUT_POST, 'iamhuman');
                        if (1 === $iamhuman || '1' === $iamhuman) {
                            // if user checked on i am human field.
                            return true;
                        } else {
                            // if user not checked on iam human field.
                            status_header(400);
                            $output['disableAntibotForm'] = true;
                            $output['form_result'] = 'error';
                            $output['form_result_msg'] = __('You are not authorized to download the file. Failed to validate human.', 'rd-downloads');

                            $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
                            $RdDownloadLogs->writeLog('user_dl_antbotfailed', ['download_id' => $download_id]);
                            unset($RdDownloadLogs);
                        }
                        unset($iamhuman);
                    }
                }// endif; method
                unset($requestMethod);
            }// endif; validated cookie test

            $this->Loader->loadTemplate('RdDownloadsPage/subUseAntibot_v', $output);
            unset($output);

            return false;
        }// subUseAntibot


    }
}
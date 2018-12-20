<?php
/**
 * Front-end download process page.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Front\\RdDownloadsPage')) {
    /**
     * Process the download.
     * 
     * This class was called from `App\Controllers\Front\Hooks\Query\DownloadPage` class -> `goToRdDownloadsPage()` method.
     * 
     * @todo [rd-downloads] add to download_logs for user download success, user download error, user banned.
     */
    class RdDownloadsPage
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
         * Do force download.
         * 
         * This method contain `exit()` function, after call to this method the process will stopped.
         * 
         * @todo [rd-downloads] change from fopen() to readfile() ( http://php.net/manual/en/function.readfile.php ). PHP said it has no problem with memory limit.
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

            if (ob_get_level() > 0) {
                ob_end_flush();
            }

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
         * Check that the setting was set to use captcha or not.<br>
         * Check that the user agent was blocked or not.<br>
         * Check that `download_id` exists or not.<br>
         * Check that downloading file is local or not.<br>
         * Increase download count.<br>
         *  - If remote or GitHub then redirect to start download.<br>
         *  - If local then check that setting to force download (global and individual) or not. Start download after force download check.
         * 
         * @global array $rd_downloads_options
         * @param integer The `download_id` to match in DB.
         */
        public function pageIndex($download_id)
        {
            global $rd_downloads_options;

            // set page title.
            $this->setTitle(__('Rundiz Downloads', 'rd-downloads'));

            if (isset($rd_downloads_options['rdd_use_captcha']) && $rd_downloads_options['rdd_use_captcha'] == '1') {
                // if setting was set to use captcha.
                $stepCaptcha = $this->subUseCaptcha();
            } else {
                // if setting was not set to use captcha.
                $stepCaptcha = true;
            }

            if (isset($stepCaptcha) && $stepCaptcha === true) {
                // if captcha passed (or setting not to use it).
                // check for banned user agent.
                $stepCheckBannedUA = $this->subCheckBannedUA($download_id);
            }
            unset($stepCaptcha);

            if (isset($stepCheckBannedUA) && $stepCheckBannedUA === true) {
                // if not banned user agent.
                // check download exists and start download is the last step.
                $this->subGetDownloadData($download_id);
            }
            unset($stepCheckBannedUA);
        }// pageIndex


        /**
         * Set page title instead of letting it displaying "Page not found."
         * 
         * @link https://developer.wordpress.org/reference/hooks/document_title_parts/ Reference.
         * @param string $customTitle The page title.
         */
        protected function setTitle($customTitle)
        {
            if (!is_scalar($customTitle)) {
                return null;
            }

            add_filter('document_title_parts', function($title) use ($customTitle) {
                $title['title'] = $customTitle;
                return $title;
            });
        }// setTitle


        /**
         * Sub page check banned User Agent.
         * 
         * @global array $rd_downloads_options
         * @param integer $download_id The download_id field.
         * @return boolean Return true on success (not banned), return false for banned or otherwise.
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
                            return false;
                        }
                    }// endforeach;
                    unset($bannedUserAgent);
                }
                unset($bannedUserAgents, $currentUserAgent);
                return true;
            } else {
                // no banned user agent.
                return true;
            }
        }// subCheckBannedUA


        /**
         * Sub page check for download item exists and start download.
         * 
         * @global array $rd_downloads_options
         * @param integer $download_id The `download_id` that matched in DB.
         * @return boolean Return false for failure. If success then it will process the download here and exit.
         */
        protected function subGetDownloadData($download_id)
        {
            $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
            $RdDownloadLogs = new \RdDownloads\App\Models\RdDownloadLogs();
            $downloadRow = $RdDownloads->get(['download_id' => $download_id]);

            if (empty($downloadRow) || is_null($downloadRow)) {
                // if not found.
                // write download log.
                $data = [];
                $data['download_id'] = $download_id;
                $RdDownloadLogs->writeLog('user_dl_error', $data);
                unset($data, $RdDownloadLogs);

                status_header(404);
                $this->Loader->loadTemplate('RdDownloadsPage/subGetDownloadData_v');
                return false;
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
         * Sub page use captcha.
         * 
         * Show captcha and check it before go to next step.
         * 
         * @global array $rd_downloads_options
         * @return boolean Return true on success validated captcha.
         */
        protected function subUseCaptcha()
        {
            // use `do_action()` to allow other plugins, themes to use their own captcha.
            do_action('rddownloads_action_before_captcha');

            // check for validated captcha and do not enter again on every request for xx minutes.
            if (
                isset($_SESSION['rddownloads_correct_captcha_time']) &&
                current_time('timestamp') <= $_SESSION['rddownloads_correct_captcha_time']
            ) {
                // if in skip time.
                return true;
            } else {
                unset($_SESSION['rddownloads_correct_captcha_time']);
            }

            global $rd_downloads_options;

            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/securimage/securimage.php';

            if (!isset($_SESSION['rddownloads_enter_wrong_captcha'])) {
                $_SESSION['rddownloads_enter_wrong_captcha'] = 0;
            }

            $output = [];
            $output['captchaImage'] = add_query_arg([
                'pagename' => filter_input(INPUT_GET, 'pagename', FILTER_SANITIZE_STRING),
                'rddownloads_subpage' => 'securimage_captcha',
                'download_id' => false,
            ], home_url());

            if (isset($rd_downloads_options['rdd_use_captcha_audio']) && $rd_downloads_options['rdd_use_captcha_audio'] == '1') {
                // if setting was set to enable captcha audio.
                $output['captchaAudio'] = add_query_arg([
                    'pagename' => filter_input(INPUT_GET, 'pagename', FILTER_SANITIZE_STRING),
                    'rddownloads_subpage' => 'securimage_captcha',
                    'rddownloads_subpage' => 'securimage_captcha_audio',
                    'download_id' => false,
                    'id' => uniqid(),
                ], home_url());
                $output['useCaptchaAudio'] = true;
            } else {
                // if setting was set to disable captcha audio.
                $output['captchaAudio'] = '';
                $output['useCaptchaAudio'] = false;
            }

            // check for too many attempts wrong captcha.
            if (
                isset($_SESSION['rddownloads_enter_wrong_captcha_waituntil']) && 
                current_time('timestamp') <= $_SESSION['rddownloads_enter_wrong_captcha_waituntil']
            ) {
                // if in banned time.
                status_header(429);
                $output['disableCaptchaForm'] = true;
                $output['form_result'] = 'error';
                $output['form_result_msg'] = __('Too many attempts for wrong captcha code.', 'rd-downloads') . ' ' .
                    sprintf(
                        /* translators: %s: Date/time that un-banned captcha page. */
                        __('Please wait until %s and try again.', 'rd-downloads'),
                        date('Y-m-d H:i:s', $_SESSION['rddownloads_enter_wrong_captcha_waituntil'])
                    );
            } else {
                // if not in banned time.
                unset($_SESSION['rddownloads_enter_wrong_captcha_waituntil']);
                $output['disableCaptchaForm'] = false;
            }

            $input_captcha = filter_input(INPUT_POST, 'rddownloads_captcha', FILTER_SANITIZE_STRING);
            $request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING);

            if (strtolower($request_method) === 'post' && !empty($input_captcha) && is_scalar($input_captcha)) {
                // if method post and form data is not empty.
                if ($_SESSION['rddownloads_enter_wrong_captcha'] >= 5 && $_SESSION['rddownloads_enter_wrong_captcha'] <= 10) {
                    // if starting incorrect many times.
                    sleep(($_SESSION['rddownloads_enter_wrong_captcha'] - 4));// 4 because starting incorrect many times is 5, let it sleep 1 second. 9 times sleep = (9-4) = 5 seconds.
                } elseif ($_SESSION['rddownloads_enter_wrong_captcha'] > 10) {
                    $_SESSION['rddownloads_enter_wrong_captcha_waituntil'] = (current_time('timestamp') + (15 * 60));// wait for 15 minutes.
                    // and yes, if user is trying to enter again then they have to wait longer. keep current timestamp + 15 minutes for more waiting.
                }

                if (isset($output['disableCaptchaForm']) && $output['disableCaptchaForm'] === false) {
                    // if not banned too many attampts for wrong captcha.
                    $Img = new \Securimage();
                    $Img->namespace = 'rddownloads_download_page';
                    $checkResult = $Img->check($input_captcha);
                    if ($checkResult === false) {
                        // if enter wrong captcha code.
                        status_header(400);
                        $_SESSION['rddownloads_enter_wrong_captcha'] = ($_SESSION['rddownloads_enter_wrong_captcha'] + 1);
                        $output['form_result'] = 'error';
                        $output['form_result_msg'] = __('The security code entered was incorrect.', 'rd-downloads') . ' ' .
                            sprintf(
                                /* translators: %d: Number of time incorrect. */
                                _n('You had entered incorrect for %s time.', 'You had enter incorrect for %s times.', $_SESSION['rddownloads_enter_wrong_captcha'], 'rd-downloads'), 
                                $_SESSION['rddownloads_enter_wrong_captcha']
                            );
                    } else {
                        // if check captcha passed.
                        unset($_SESSION['rddownloads_enter_wrong_captcha'], $_SESSION['rddownloads_enter_wrong_captcha_waituntil']);
                        $_SESSION['rddownloads_correct_captcha_time'] = (current_time('timestamp') + (30 * 60));// add validated captcha and no need to check captcha again for xx minutes.
                        return true;
                    }
                    unset($checkResult, $Img);
                }
            } else {
                status_header(200);
            }// endif method post.

            unset($input_captcha, $request_method);

            if (isset($output['disableCaptchaForm']) && $output['disableCaptchaForm'] === false) {
                // if not banned too many attampts for wrong captcha.
                wp_enqueue_script('rd-downloads-securimage-controller', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/securimage-controller.js', ['jquery'], RDDOWNLOADS_VERSION, true);
                wp_localize_script(
                    'rd-downloads-securimage-controller',
                    'RdDownloads',
                    [
                        'captchaImageUrl' => $output['captchaImage'],
                        'captchaAudioUrl' => $output['captchaAudio'],
                        'useCaptchaAudio' => ($output['useCaptchaAudio'] === true ? 'true' : 'false'),
                    ]
                );
            }

            $this->Loader->loadTemplate('RdDownloadsPage/subUseCaptcha_v', $output);
            unset($output);

            return false;
        }// subUseCaptcha


    }
}
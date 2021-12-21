<?php
/**
 * Use GitHub OAuth to connect with this site.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Downloads;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Downloads\\GithubOAuth')) {
    /**
     * Use GitHub OAuth to connect with this site.
     *
     * @link https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/ GitHub OAuth reference.
     */
    class GithubOAuth
    {


        use \RdDownloads\App\AppTrait;


        /**
         * @var string|object Contain string of access token, contain object if error, empty string if it was not set.
         * @see \RdDownloads\App\Libraries\Github::oauthGetAccessToken()
         */
        protected $accessToken;


        /**
         * @var integer Current GitHub connect (OAuth) step.
         */
        protected $connectStep = 1;


        /**
         * @var integer Current user ID.
         * @see get_current_user_id()
         */
        protected $currentUserId;


        /**
         * @var \RdDownloads\App\Libraries\Github GitHub class.
         */
        protected $Github;


        /**
         * @var string WordPress nonce action name. Do not change this if possible.
         */
        protected $nonceAction = 'rd-downloads_github-api-nonce';


        /**
         * @var string URL to this GitHub connect page.
         */
        protected $thisPageUrl;


        /**
         * @var false|integer  False if the nonce is invalid, 1 if the nonce is valid and generated between 0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
         * @see wp_verify_nonce()
         */
        protected $verifyNonce = false;


        public function __construct()
        {
            $this->Github = new \RdDownloads\App\Libraries\Github();
        }// __construct


        /**
         * Display admin help tab.
         */
        public function adminHelpTab()
        {
            $screen = get_current_screen();
            $Loader = new \RdDownloads\App\Libraries\Loader();

            ob_start();
            $Loader->loadView('admin/Downloads/GithubOAuth/helpTab/permission_v');
            $content = ob_get_contents();
            ob_end_clean();
            $screen->add_help_tab([
                'id' => 'rd-downloads-logs-helptab-permission',
                'title' => __('Permissions', 'rd-downloads'),
                'content' => $content,
            ]);
            unset($content);

            if (current_user_can('manage_options')) {
                ob_start();
                $Loader->loadView('admin/Downloads/GithubOAuth/helpTab/webhook_v');
                $content = ob_get_contents();
                ob_end_clean();
                $screen->add_help_tab([
                    'id' => 'rd-downloads-logs-helptab-webhook',
                    'title' => __('GitHub Webhook', 'rd-downloads'),
                    'content' => $content,
                ]);
                unset($content);
            }

            unset($Loader);
        }// adminHelpTab


        /**
         * Display "GitHub OAuth" sub-menu inside "Downloads" menu.
         *
         * @global array $rd_downloads_options
         */
        public function githubOAuthMenu()
        {
            $this->getOptions();
            global $rd_downloads_options;

            if (
                isset($rd_downloads_options['rdd_github_client_id']) &&
                isset($rd_downloads_options['rdd_github_client_secret']) &&
                !empty($rd_downloads_options['rdd_github_client_id']) &&
                !empty($rd_downloads_options['rdd_github_client_secret'])
            ) {
                $hook_suffix = add_submenu_page('rd-downloads', __('GitHub OAuth', 'rd-downloads'), __('GitHub OAuth', 'rd-downloads'), 'upload_files', 'rd-downloads_github_connect', [$this, 'pageIndex']);
                add_action('load-' . $hook_suffix, [$this, 'headerWorks']);
                add_action('load-' . $hook_suffix, [$this, 'adminHelpTab']);
                add_action('admin_print_styles-' . $hook_suffix, [$this, 'registerStyles']);
                add_action('admin_print_styles-' . $hook_suffix, [$this, 'registerScripts']);
                unset($hook_suffix);
            }
        }// githubOAuthMenu


        /**
         * Header work for sub page disconnect.
         */
        protected function headerSubPageDisconnect()
        {
            if ($_POST) {
                if (!check_admin_referer('rddownloads_github_disconnect', 'rddownloads_github_disconnect')) {
                    wp_nonce_ays('rddownloads_github_disconnect');
                }

                $this->Github->oauthDisconnect($this->currentUserId);

                wp_safe_redirect($this->thisPageUrl . '&subpage=disconnect');
                exit();
            }
        }// headerSubPageDisconnect


        /**
         * Working about header such as set cookie, session, redirection.
         *
         * This method will be called before `pageIndex()` method.
         * If this method work, it maybe end process.
         *
         * @link https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/ GitHub OAuth reference.
         */
        public function headerWorks()
        {
            // check permission.
            if (!current_user_can('upload_files')) {
                wp_die(__('You do not have permission to access this page.'), '', ['response' => 403]);
            }

            $this->thisPageUrl = admin_url('admin.php?page=rd-downloads_github_connect');
            $this->currentUserId = get_current_user_id();

            $subpage = filter_input(INPUT_GET, 'subpage');
            $subpage = (is_string($subpage) ? htmlspecialchars($subpage, ENT_QUOTES) : $subpage);
            if ($subpage === 'disconnect' && $_POST) {
                return $this->headerSubPageDisconnect();
            }

            $githubReturnCode = filter_input(INPUT_GET, 'code');
            if (is_string($githubReturnCode)) {
                $githubReturnCode = strip_tags($githubReturnCode);
            }
            $githubReturnState = filter_input(INPUT_GET, 'state');
            if (is_string($githubReturnState)) {
                $githubReturnState = strip_tags($githubReturnState);
            }
            $accessTokenCookie = filter_input(INPUT_COOKIE, $this->Github->getOAuthAccessTokenName());
            if (is_string($accessTokenCookie)) {
                $accessTokenCookie = strip_tags($accessTokenCookie);
            }
            if (!empty($accessTokenCookie)) {
                // if contain access token cookie
                $accessTokenUserDb = $this->Github->getOAuthAccessToken($this->currentUserId);
                if ($accessTokenUserDb !== $accessTokenCookie) {
                    // if access token from cookie does not match in db.
                    $this->Github->oauthDisconnect($this->currentUserId);
                    unset($accessTokenCookie, $accessTokenUserDb, $githubReturnCode, $githubReturnState);

                    wp_safe_redirect($this->thisPageUrl);
                    exit();
                } else {
                    $this->accessToken = $accessTokenCookie;
                }
                unset($accessTokenUserDb);
            }

            if ((empty($githubReturnCode) || empty($githubReturnState)) && empty($accessTokenCookie)) {
                // if no return code OR state from github. AND no token cookie.
                // it is 1st step. (get link to login using GitHub.)
                $this->connectStep = 1;
            } elseif (!empty($githubReturnCode) && !empty($githubReturnState) && empty($accessTokenCookie)) {
                // if contain return code AND state from github AND no token cookie.
                // it is 2nd step. (got code and state from GitHub, use the code to get access token.)
                $this->connectStep = 2;
            } elseif ((empty($githubReturnCode) && empty($githubReturnState)) && !empty($accessTokenCookie)) {
                // if no return code AND state from github AND contain token cookie.
                // it is 3rd step. (list user's repository to let user make sure that it is correct.)
                $this->connectStep = 3;
            } else {
                $this->connectStep = 1;
            }

            if ($this->connectStep == 2) {
                // if 2nd step, get access token from the code.
                $this->verifyNonce = wp_verify_nonce($githubReturnState, $this->nonceAction);
                if ($this->verifyNonce !== false) {
                    $this->accessToken = $this->Github->oauthGetAccessToken($githubReturnCode, $this->thisPageUrl, $githubReturnState);

                    if (is_string($this->accessToken) && !empty($this->accessToken)) {
                        update_user_meta($this->currentUserId, $this->Github->getOAuthAccessTokenName(), $this->accessToken);
                        $userGitHubSecret = get_user_meta($this->currentUserId, $this->Github->getWebhookSecretName(), true);
                        if (empty($userGitHubSecret) || $userGitHubSecret === false) {
                            update_user_meta($this->currentUserId, $this->Github->getWebhookSecretName(), $this->Github->generateWebhookSecretKey($this->currentUserId));
                        }
                        unset($userGitHubSecret);

                        \RdDownloads\App\Libraries\Cookies::setCookie($this->Github->getOAuthAccessTokenName(), $this->accessToken);
                        unset($accessTokenCookie, $githubReturnCode, $githubReturnState);

                        wp_safe_redirect($this->thisPageUrl);
                        exit();
                    }
                }
            }// endif; check what is current step.

            unset($accessTokenCookie, $githubReturnCode, $githubReturnState);
        }// headerWorks


        /**
         * Display GitHub OAuth (connect) page.
         */
        public function pageIndex()
        {
            // check permission.
            if (!current_user_can('upload_files')) {
                wp_die(__('You do not have permission to access this page.'), '', ['response' => 403]);
            }

            $subpage = filter_input(INPUT_GET, 'subpage');
            if (is_string($subpage)) {
                $subpage = strip_tags($subpage);
            }
            if ($subpage === 'disconnect') {
                return $this->subPageDisconnect();
            }

            // preset output value to views.
            $output = [];
            $output['accessToken'] = (is_string($this->accessToken) ? $this->accessToken : '');
            $output['thisPageUrl'] = $this->thisPageUrl;

            if ($this->connectStep == 1) {
                // if 1st step, get the link.
                $output['githubOAuthLink'] = $this->Github->oauthGetLink($output['thisPageUrl'], wp_create_nonce($this->nonceAction));
            } elseif ($this->connectStep == 2) {
                // if 2nd step, get access token from the code.
                if (!$this->verifyNonce) {
                    // if failed to verify nonce.
                    $output['form_result_class'] = 'notice-error';
                    /* translators: %1$s: Open link, %2$s: Close link. */
                    $output['form_result_msg'] = sprintf(__('Please %1$sgo back%2$s and try again.', 'rd-downloads'), '<a href="' . esc_url($output['thisPageUrl']) . '">', '</a>');
                } else {
                    // if success verify nonce.
                    if (is_object($this->accessToken) && isset($this->accessToken->error_description) && isset($this->accessToken->error_uri)) {
                        // if get access token and contain error.
                        $output['form_result_class'] = 'notice-error';
                        $output['form_result_msg'] = $this->accessToken->error_description;
                        /* translators: %1$s: Open link, %2$s: The link URL, %3$s: Close link. */
                        $output['form_result_msg'] .= '<br>' . sprintf(__('%1$s%2$s%3$s.', 'rd-downloads'), '<a href="' . $this->accessToken->error_uri . '" target="github_accesstoken_error">', $this->accessToken->error_uri, '</a>');
                        /* translators: %1$s: Open link, %2$s: Close link. */
                        $output['form_result_msg'] .= '<br><br>' . sprintf(__('Please %1$sgo back%2$s and try again.', 'rd-downloads'), '<a href="' . esc_url($output['thisPageUrl']) . '">', '</a>');
                    }
                }
            } elseif ($this->connectStep == 3) {
                // if 3rd step, list user's repositories.
                $GitHubOAuthListTable = new \RdDownloads\App\Models\GitHubOAuthListTable();
                $prepareResult = $GitHubOAuthListTable->prepare_items([
                    'Github' => $this->Github,
                    'accessToken' => $this->accessToken,
                    'userId' => $this->currentUserId
                ]);
                $output['GitHubOAuthListTable'] = $GitHubOAuthListTable;
                unset($GitHubOAuthListTable);

                if (
                    isset($prepareResult['responseHeader']['status-int']) &&
                    (
                        $prepareResult['responseHeader']['status-int'] < 200 ||
                        $prepareResult['responseHeader']['status-int'] >= 300
                    )
                ) {
                    // if contain error from GitHub.
                    if (isset($prepareResult['responseBody']->message)) {
                        $output['form_result_class'] = 'notice-error';
                        $output['form_result_msg'] = $prepareResult['responseBody']->message;
                        /* translators: %1$s: Open link, %2$s: Close link. */
                        $output['form_result_msg'] .= '<br><br>' . sprintf(__('Please %1$sdisconnect from GitHub OAuth%2$s and try again.', 'rd-downloads'), '<a href="' . esc_url(admin_url('admin.php?page=rd-downloads_github_connect&subpage=disconnect')) . '">', '</a>');
                    }
                } else {
                    if (is_array($prepareResult)) {
                        $output = array_merge($output, $prepareResult);
                    }
                    $output['githubSecret'] = get_user_meta($this->currentUserId, $this->Github->getWebhookSecretName(), true);
                }

                unset($prepareResult);
            }// endif; check what is current step.

            $Loader = new \RdDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/GithubOAuth/pageIndex_v', $output);
            unset($Loader);
        }// pageIndex


        /**
         * Enqueue JS.
         */
        public function registerScripts()
        {
            wp_enqueue_script('rd-downloads-github-connect-page-index', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/admin/Downloads/GithubOAuth/pageIndex.js', ['jquery', 'rd-downloads-common-functions'], RDDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rd-downloads-github-connect-page-index',
                'RdDownloads',
                [
                    'currentUserId' => $this->currentUserId,
                    'nonce' => wp_create_nonce($this->nonceAction),
                    'txtAreYouSureRegenerateSecret' => __('Are you sure?', 'rd-downloads') . "\n\n" . __('You have to sync the secret key with your GitHub repositories again to make the auto update works.', 'rd-downloads'),
                    'txtExists' => __('Exists', 'rd-downloads'),
                    'txtNotExists' => __('Not exists', 'rd-downloads'),
                    'txtRegenerating' => __('Re-generating the secret key, please wait.', 'rd-downloads'),
                    'txtSyncing' => __('Synchronizing, please wait.', 'rd-downloads'),
                ]
            );
        }// registerScripts


        /**
         * Enqueue CSS.
         */
        public function registerStyles()
        {
            wp_enqueue_style('rd-downloads-font-awesome5');
            wp_enqueue_style('rd-downloads-github-connect-page-index', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/css/admin/Downloads/GithubOAuth/pageIndex.css', [], RDDOWNLOADS_VERSION);
        }// registerStyles


        /**
         * Disconnect from GitHub OAuth page.
         *
         * This page was called from `pageIndex()` method.
         */
        protected function subPageDisconnect()
        {
            $output = [];

            if ($this->Github->isOAuthDisconnected()) {
                $output['disconnected'] = true;
            }

            $output['thisPageUrl'] = $this->thisPageUrl;

            $Loader = new \RdDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/GithubOAuth/subPageDisconnect_v', $output);
            unset($Loader, $output);
        }// subPageDisconnect


    }
}
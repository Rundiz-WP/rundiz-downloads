<?php
/**
 * Use GitHub OAuth to connect with this site.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Downloads;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Downloads\\GithubOAuth')) {
    /**
     * Use GitHub OAuth to connect with this site.
     *
     * @link https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/ GitHub OAuth reference.
     */
    class GithubOAuth
    {


        use \RundizDownloads\App\AppTrait;


        /**
         * @var This menu slug (sub menu). This constant must be public.
         */
        const MENU_SLUG = 'rundiz-downloads_github_connect';


        /**
         * @var string|object Contain string of access token, contain object if error, empty string if it was not set.
         * @see \RundizDownloads\App\Libraries\Github::oauthGetAccessToken()
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
         * @var \RundizDownloads\App\Libraries\Github GitHub class.
         */
        protected $Github;


        /**
         * @var string|false WordPress page's hook suffix that have got from function `add_[sub]menu_page()`.
         */
        protected $hook_suffix = false;


        /**
         * @var string WordPress nonce action name. Do not change this if possible.
         */
        protected $nonceAction = 'rundiz-downloads_github-api-nonce';


        /**
         * @var string URL to this GitHub connect page.
         */
        protected $thisPageUrl;


        /**
         * @var false|integer  False if the nonce is invalid, 1 if the nonce is valid and generated between 0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
         * @see wp_verify_nonce()
         */
        protected $verifyNonce = false;


        /**
         * GitHubOAuth class constructor.
         */
        public function __construct()
        {
            $this->Github = new \RundizDownloads\App\Libraries\Github();
        }// __construct


        /**
         * Display admin help tab.
         */
        public function adminHelpTab()
        {
            $screen = get_current_screen();
            $Loader = new \RundizDownloads\App\Libraries\Loader();

            ob_start();
            $Loader->loadView('admin/Downloads/GithubOAuth/helpTab/permission_v');
            $content = ob_get_contents();
            ob_end_clean();
            $screen->add_help_tab([
                'id' => 'rundiz-downloads-github-oauth-helptab-permission',
                'title' => __('Permissions', 'rundiz-downloads'),
                'content' => $content,
            ]);
            unset($content);

            if (current_user_can('manage_options')) {
                ob_start();
                $Loader->loadView('admin/Downloads/GithubOAuth/helpTab/webhook_v');
                $content = ob_get_contents();
                ob_end_clean();
                $screen->add_help_tab([
                    'id' => 'rundiz-downloads-logs-helptab-webhook',
                    'title' => __('GitHub Webhook', 'rundiz-downloads'),
                    'content' => $content,
                ]);
                unset($content);
            }

            unset($Loader);
        }// adminHelpTab


        /**
         * Allow code/WordPress to call hook `admin_enqueue_scripts` 
         * then `wp_register_script()`, `wp_localize_script()`, `wp_enqueue_script()` functions will be working fine later.
         * 
         * @link https://wordpress.stackexchange.com/a/76420/41315 Original source code.
         */
        public function callEnqueueHook()
        {
            add_action('admin_enqueue_scripts', [$this, 'registerStyles']);
            add_action('admin_enqueue_scripts', [$this, 'registerScripts']);
        }// callEnqueueHook


        /**
         * Display "GitHub OAuth" sub-menu inside "Downloads" menu.
         *
         * @global array $rundiz_downloads_options
         */
        public function githubOAuthMenu()
        {
            $this->getOptions();
            global $rundiz_downloads_options;

            if (
                isset($rundiz_downloads_options['rdd_github_client_id']) &&
                isset($rundiz_downloads_options['rdd_github_client_secret']) &&
                !empty($rundiz_downloads_options['rdd_github_client_id']) &&
                !empty($rundiz_downloads_options['rdd_github_client_secret'])
            ) {
                $hook_suffix = add_submenu_page(Menu::MENU_SLUG, __('GitHub OAuth', 'rundiz-downloads'), __('GitHub OAuth', 'rundiz-downloads'), 'upload_files', self::MENU_SLUG, [$this, 'pageIndex']);
                $this->hook_suffix = $hook_suffix;
                if (is_string($hook_suffix)) {
                    add_action('load-' . $hook_suffix, [$this, 'headerWorks']);
                    add_action('load-' . $hook_suffix, [$this, 'adminHelpTab']);
                    add_action('load-' . $hook_suffix, [$this, 'callEnqueueHook']);
                }
                unset($hook_suffix);
            }
        }// githubOAuthMenu


        /**
         * Header work for sub page disconnect.
         */
        protected function headerSubPageDisconnect()
        {
            if (isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD']) {
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
                wp_die(esc_html__('You do not have permission to access this page.', 'rundiz-downloads'), '', ['response' => 403]);
            }

            $this->thisPageUrl = admin_url('admin.php?page=' . self::MENU_SLUG);
            $this->currentUserId = get_current_user_id();

            $subpage = filter_input(INPUT_GET, 'subpage');
            $subpage = (is_string($subpage) ? htmlspecialchars($subpage, ENT_QUOTES) : $subpage);
            if ('disconnect' === $subpage && isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD']) {
                return $this->headerSubPageDisconnect();
            }

            $githubReturnCode = filter_input(INPUT_GET, 'code');
            if (is_string($githubReturnCode)) {
                $githubReturnCode = wp_strip_all_tags($githubReturnCode);
            }
            $githubReturnState = filter_input(INPUT_GET, 'state');
            if (is_string($githubReturnState)) {
                $githubReturnState = wp_strip_all_tags($githubReturnState);
            }
            $accessTokenCookie = filter_input(INPUT_COOKIE, $this->Github->getOAuthAccessTokenName());
            if (is_string($accessTokenCookie)) {
                $accessTokenCookie = wp_strip_all_tags($accessTokenCookie);
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

            if (2 === $this->connectStep) {
                // if 2nd step, get access token from the code.
                $this->verifyNonce = wp_verify_nonce($githubReturnState, $this->nonceAction);
                if (false !== $this->verifyNonce) {
                    $this->accessToken = $this->Github->oauthGetAccessToken($githubReturnCode, $this->thisPageUrl, $githubReturnState);

                    if (is_string($this->accessToken) && !empty($this->accessToken)) {
                        update_user_meta($this->currentUserId, $this->Github->getOAuthAccessTokenName(), $this->accessToken);
                        $userGitHubSecret = get_user_meta($this->currentUserId, $this->Github->getWebhookSecretName(), true);
                        if (empty($userGitHubSecret) || false === $userGitHubSecret) {
                            update_user_meta($this->currentUserId, $this->Github->getWebhookSecretName(), $this->Github->generateWebhookSecretKey($this->currentUserId));
                        }
                        unset($userGitHubSecret);

                        \RundizDownloads\App\Libraries\Cookies::setCookie($this->Github->getOAuthAccessTokenName(), $this->accessToken);
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
                wp_die(esc_html__('You do not have permission to access this page.', 'rundiz-downloads'), '', ['response' => 403]);
            }

            $subpage = filter_input(INPUT_GET, 'subpage');
            if (is_string($subpage)) {
                $subpage = wp_strip_all_tags($subpage);
            }
            if ('disconnect' === $subpage) {
                return $this->subPageDisconnect();
            }

            // preset output value to views.
            $output = [];
            $output['accessToken'] = (is_string($this->accessToken) ? $this->accessToken : '');
            $output['thisPageUrl'] = $this->thisPageUrl;

            if (1 === $this->connectStep) {
                // if 1st step, get the link.
                $output['githubOAuthLink'] = $this->Github->oauthGetLink($output['thisPageUrl'], wp_create_nonce($this->nonceAction));
            } elseif (2 === $this->connectStep) {
                // if 2nd step, get access token from the code.
                if (!$this->verifyNonce) {
                    // if failed to verify nonce.
                    $output['form_result_class'] = 'notice-error';
                    /* translators: %1$s: Open link, %2$s: Close link. */
                    $output['form_result_msg'] = sprintf(__('Please %1$sgo back%2$s and try again.', 'rundiz-downloads'), '<a href="' . esc_url($output['thisPageUrl']) . '">', '</a>');
                } else {
                    // if success verify nonce.
                    if (is_object($this->accessToken) && isset($this->accessToken->error_description) && isset($this->accessToken->error_uri)) {
                        // if get access token and contain error.
                        $output['form_result_class'] = 'notice-error';
                        $output['form_result_msg'] = $this->accessToken->error_description;
                        /* translators: %1$s: Open link, %2$s: The link URL, %3$s: Close link. */
                        $output['form_result_msg'] .= '<br>' . sprintf(__('%1$s%2$s%3$s.', 'rundiz-downloads'), '<a href="' . $this->accessToken->error_uri . '" target="github_accesstoken_error">', $this->accessToken->error_uri, '</a>');
                        /* translators: %1$s: Open link, %2$s: Close link. */
                        $output['form_result_msg'] .= '<br><br>' . sprintf(__('Please %1$sgo back%2$s and try again.', 'rundiz-downloads'), '<a href="' . esc_url($output['thisPageUrl']) . '">', '</a>');
                    }
                }
            } elseif (3 === $this->connectStep) {
                // if 3rd step, list user's repositories.
                $GitHubOAuthListTable = new \RundizDownloads\App\Models\GitHubOAuthListTable();
                $prepareResult = $GitHubOAuthListTable->prepare_items([
                    'Github' => $this->Github,
                    'accessToken' => $this->accessToken,
                    'userId' => $this->currentUserId,
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
                        $output['form_result_msg'] .= '<br><br>' . sprintf(__('Please %1$sdisconnect from GitHub OAuth%2$s and try again.', 'rundiz-downloads'), '<a href="' . esc_url(admin_url('admin.php?page=' . self::MENU_SLUG . '&subpage=disconnect')) . '">', '</a>');
                    }
                } else {
                    if (is_array($prepareResult)) {
                        $output = array_merge($output, $prepareResult);
                    }
                    $output['githubSecret'] = get_user_meta($this->currentUserId, $this->Github->getWebhookSecretName(), true);
                }

                unset($prepareResult);
            }// endif; check what is current step.

            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/GithubOAuth/pageIndex_v', $output);
            unset($Loader);
        }// pageIndex


        /**
         * Enqueue JS.
         * 
         * @param string $hook_suffix The current admin page.
         */
        public function registerScripts($hook_suffix)
        {
            if (!is_string($hook_suffix) || $this->hook_suffix !== $hook_suffix) {
                return;
            }

            wp_enqueue_script('rundiz-downloads-github-connect-page-index-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/admin/Downloads/GithubOAuth/pageIndex.js', ['jquery', 'rundiz-downloads-common-functions-js'], RUNDIZDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rundiz-downloads-github-connect-page-index-js',
                'RdDownloads',
                [
                    'currentUserId' => $this->currentUserId,
                    'nonce' => wp_create_nonce($this->nonceAction),
                    'txtAreYouSureRegenerateSecret' => __('Are you sure?', 'rundiz-downloads') . "\n\n" . __('You have to sync the secret key with your GitHub repositories again to make the auto update works.', 'rundiz-downloads'),
                    'txtExists' => __('Exists', 'rundiz-downloads'),
                    'txtNotExists' => __('Not exists', 'rundiz-downloads'),
                    'txtRegenerating' => __('Re-generating the secret key, please wait.', 'rundiz-downloads'),
                    'txtSyncing' => __('Synchronizing, please wait.', 'rundiz-downloads'),
                ]
            );
        }// registerScripts


        /**
         * Enqueue CSS.
         * 
         * @param string $hook_suffix The current admin page.
         */
        public function registerStyles($hook_suffix)
        {
            if (!is_string($hook_suffix) || $this->hook_suffix !== $hook_suffix) {
                return;
            }

            wp_enqueue_style('rundiz-downloads-font-awesome5');
            wp_enqueue_style('rundiz-downloads-github-connect-page-index-css', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/css/admin/Downloads/GithubOAuth/pageIndex.css', [], RUNDIZDOWNLOADS_VERSION);
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

            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/GithubOAuth/subPageDisconnect_v', $output);
            unset($Loader, $output);
        }// subPageDisconnect


    }// GithubOAuth
}

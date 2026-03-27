<?php
/**
 * Rundiz Downloads management page.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Downloads;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Downloads\\Management')) {
    /**
     * Management class.
     */
    class Management
    {


        /**
         * @var string|false WordPress page's hook suffix that have got from function `add_[sub]menu_page()`.
         */
        public $hook_suffix = false;


        /**
         * Add the screen options for management page.
         */
        public function addScreenOptions()
        {
            add_screen_option('per_page', 
                [
                    'label' => __('Number of items per page:', 'rundiz-downloads'),
                    'default' => 20,
                    'option' => 'rddownloads_items_perpage',// require alpha-numeric, underscore (_). no dash (-) allowed.
                ]
            );
        }// addScreenOptions


        /**
         * Display admin help tab.
         */
        public function adminHelpTab()
        {
            $screen = get_current_screen();
            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $wp_upload_dir = wp_upload_dir();

            $output = [];
            $output['basedir'] = (isset($wp_upload_dir['basedir']) ? $wp_upload_dir['basedir'] : '');
            unset($wp_upload_dir);

            ob_start();
            $Loader->loadView('admin/Downloads/Management/helpTab/shortcodes_v', $output);
            $content = ob_get_contents();
            ob_end_clean();
            $screen->add_help_tab([
                'id' => 'rundiz-downloads-listing-helptab-shortcodes',
                'title' => __('Shortcodes', 'rundiz-downloads'),
                'content' => $content,
            ]);
            unset($content);

            ob_start();
            $Loader->loadView('admin/Downloads/Management/helpTab/permission_v', $output);
            $content = ob_get_contents();
            ob_end_clean();
            $screen->add_help_tab([
                'id' => 'rundiz-downloads-listing-helptab-permission',
                'title' => __('Permissions', 'rundiz-downloads'),
                'content' => $content,
            ]);
            unset($content);

            ob_start();
            $Loader->loadView('admin/Downloads/Management/helpTab/adminhelp_v');
            $content = ob_get_contents();
            ob_end_clean();
            $screen->add_help_tab([
                'id' => 'rundiz-downloads-listing-helptab-adminhelp',
                'title' => __('Admin help', 'rundiz-downloads'),
                'content' => $content,
            ]);
            unset($content);

            unset($Loader, $output);
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
         * Filter screen option.
         * 
         * This method was called from `add_filter()` function in `Menu` class.
         * 
         * @link https://developer.wordpress.org/reference/hooks/set-screen-option/ Reference.
         * @param boolean|integer $status Screen option value. Default false to skip.
         * @param string $option The option name.
         * @param integer $value The number of rows to use.
         * @return integer Return the validated value.
         */
        public function filterScreenOption($status, $option, $value)
        {
            if ('rddownloads_items_perpage' === $option) {
                $value = intval($value);
                if ($value <= 0) {
                    $value = 20;
                } elseif ($value > 100) {
                    $value = 100;
                }
                $value = strval($value);
            }

            return $value;
        }// filterScreenOption


        /**
         * Downloads management listing page.
         * 
         * This method was called from Menu class.
         */
        public function pageIndex()
        {
            // check permission.
            if (!current_user_can('edit_posts')) {
                wp_die(esc_html__('You do not have permission to access this page.', 'rundiz-downloads'), '', ['response' => 403]);
            }

            // preset output value to views.
            $output = [];

            // initialize list table model.
            $RdDownloadsListTable = new \RundizDownloads\App\Models\RdDownloadsListTable();
            $options = [];
            if (isset($_REQUEST['filter_user_id']) && trim(wp_unslash($_REQUEST['filter_user_id'])) !== '') {// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                $options['user_id'] = intval(wp_unslash($_REQUEST['filter_user_id']));// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            }
            if (isset($_REQUEST['filter_download_type']) && trim(wp_unslash($_REQUEST['filter_download_type'])) !== '') {// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                $options['download_type'] = sanitize_text_field(wp_unslash($_REQUEST['filter_download_type']));// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            }
            if (isset($_REQUEST['s']) && trim(wp_unslash($_REQUEST['s'])) !== '') {// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                // the s is forced by WordPress.
                $options['search'] = sanitize_text_field(wp_unslash($_REQUEST['s']));// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            }
            if (isset($_REQUEST['orderby'])) {// phpcs:ignore WordPress.Security.NonceVerification.Recommended
                // the orderby is forced by WordPress.
                $options['sort'] = sanitize_text_field(wp_unslash($_REQUEST['orderby']));// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            }
            if (isset($_REQUEST['order'])) {// phpcs:ignore WordPress.Security.NonceVerification.Recommended
                // the order is forced by WordPress.
                $options['order'] = sanitize_text_field(wp_unslash($_REQUEST['order']));// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            }
            $RdDownloadsListTable->prepare_items($options);
            unset($options);

            $output['RdDownloadsListTable'] = $RdDownloadsListTable;
            unset($RdDownloadsListTable);

            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/Management/pageIndex_v', $output);
            unset($Loader);
        }// pageIndex


        /**
         * Redirect to remove ugly querystring that is not necessary with the page process.
         */
        public function redirectNiceUrl()
        {
            $unwanted_querystring = ['_wpnonce', '_wp_http_referer', 'action', 'action2'];

            $current_query_names = array_map('strtolower', array_keys($_GET));// phpcs:ignore WordPress.Security.NonceVerification.Recommended

            foreach ($current_query_names as $name) {
                if (in_array(strtolower($name), $unwanted_querystring, true)) {
                    unset($current_query_names);
                    wp_safe_redirect(remove_query_arg($unwanted_querystring));
                    exit();
                }
            }// endforeach;
            unset($name);

            unset($current_query_names, $unwanted_querystring);
        }// redirectNiceUrl


        /**
         * Enqueue scripts here.
         * 
         * @param string $hook_suffix The current admin page.
         */
        public function registerScripts($hook_suffix)
        {
            if (!is_string($hook_suffix) || $this->hook_suffix !== $hook_suffix) {
                return;
            }

            wp_enqueue_script('rundiz-downloads-manage-form-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/admin/Downloads/Management/pageIndex.js', ['rundiz-downloads-common-functions-js'], RUNDIZDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rundiz-downloads-manage-form-js',
                'RdDownloads',
                [
                    'nonce' => wp_create_nonce('rundiz-downloads_ajax-manage-nonce'),
                    'txtAreYouSureDelete' => __('Are you sure?', 'rundiz-downloads') . "\n" . __('The selected files will be deleted. GitHub and any remote files will be remain.', 'rundiz-downloads') . "\n" . __('This can not be un-done.', 'rundiz-downloads'),
                ]
            );
        }// registerScripts


        /**
         * Enqueue styles here.
         * 
         * @param string $hook_suffix The current admin page.
         */
        public function registerStyles($hook_suffix)
        {
            if (!is_string($hook_suffix) || $this->hook_suffix !== $hook_suffix) {
                return;
            }

            wp_enqueue_style('rundiz-downloads-font-awesome5');

            wp_enqueue_style('rundiz-downloads-manage-form-css', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/css/admin/Downloads/Management/pageIndex.css', [], RUNDIZDOWNLOADS_VERSION);
        }// registerStyles


    }// Management
}

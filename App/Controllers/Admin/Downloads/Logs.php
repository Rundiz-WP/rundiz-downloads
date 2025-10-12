<?php
/**
 * Download logs.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Downloads;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Downloads\\Logs')) {
    class Logs implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * @var string|false WordPress page's hook suffix that have got from function `add_[sub]menu_page()`.
         */
        protected $hook_suffix = false;


        /**
         * Add the screen options for logs page.
         */
        public function addScreenOptions()
        {
            add_screen_option('per_page', 
                [
                    'label' => __('Number of items per page:'),
                    'default' => 20,
                    'option' => 'rddownloads_logs_items_perpage',// require alpha-numeric, underscore (_). no dash (-) allowed.
                ]
            );
        }// addScreenOptions


        /**
         * Display admin help tab.
         */
        public function adminHelpTab()
        {
            $screen = get_current_screen();
            $Loader = new \RdDownloads\App\Libraries\Loader();

            $output = [];

            ob_start();
            $Loader->loadView('admin/Downloads/Logs/helpTab/permission_v', $output);
            $content = ob_get_contents();
            ob_end_clean();
            unset($output);
            $screen->add_help_tab([
                'id' => 'rd-downloads-logs-helptab-permission',
                'title' => __('Permissions', 'rd-downloads'),
                'content' => $content,
            ]);
            unset($content);

            ob_start();
            $Loader->loadView('admin/Downloads/Logs/helpTab/adminhelp_v');
            $content = ob_get_contents();
            ob_end_clean();
            $screen->add_help_tab([
                'id' => 'rd-downloads-logs-helptab-adminhelp',
                'title' => __('Admin help', 'rd-downloads'),
                'content' => $content,
            ]);
            unset($content);

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
            add_action('admin_enqueue_scripts', [$this, 'registerScripts']);
        }// callEnqueueHook


        /**
         * Display "Logs" sub-menu inside "Downloads" menu.
         */
        public function downloadLogsMenu()
        {
            $hook_suffix = add_submenu_page('rd-downloads', __('Download logs', 'rd-downloads'), __('Logs', 'rd-downloads'), 'upload_files', 'rd-downloads_logs', [$this, 'pageIndex']);
            $this->hook_suffix = $hook_suffix;
            if (is_string($hook_suffix)) {
                add_action('load-' . $hook_suffix, [$this, 'redirectNiceUrl']);
                add_action('load-' . $hook_suffix, [$this, 'addScreenOptions']);
                add_action('load-' . $hook_suffix, [$this, 'adminHelpTab']);
                add_action('load-' . $hook_suffix, [$this, 'callEnqueueHook']);
            }
            unset($hook_suffix);
        }// downloadLogsMenu


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
            if ('rddownloads_logs_items_perpage' === $option) {
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
         * Display logs page.
         */
        public function pageIndex()
        {
            // check permission.
            if (!current_user_can('upload_files')) {
                wp_die(esc_html__('You do not have permission to access this page.'), '', ['response' => 403]);
            }

            // preset output value to views.
            $output = [];

            // initialize list table model
            $RdDownloadLogsListTable = new \RdDownloads\App\Models\RdDownloadLogsListTable();
            $options = [];
            if (isset($_REQUEST['filter_user_id']) && trim($_REQUEST['filter_user_id']) !== '') {// phpcs:ignore
                $options['user_id'] = intval(wp_unslash($_REQUEST['filter_user_id']));
            }
            if (isset($_REQUEST['filter_download_id']) && trim($_REQUEST['filter_download_id']) !== '') {// phpcs:ignore
                $options['download_id'] = sanitize_text_field(wp_unslash($_REQUEST['filter_download_id']));
            }
            if (isset($_REQUEST['s']) && trim($_REQUEST['s']) !== '') {// phpcs:ignore
                // the s is forced by WordPress.
                $options['search'] = sanitize_text_field(wp_unslash($_REQUEST['s']));
            }
            if (isset($_REQUEST['orderby'])) {
                // the orderby is forced by WordPress.
                $options['sort'] = sanitize_text_field(wp_unslash($_REQUEST['orderby']));
            }
            if (isset($_REQUEST['order'])) {
                // the order is forced by WordPress.
                $options['order'] = sanitize_text_field(wp_unslash($_REQUEST['order']));
            }
            $RdDownloadLogsListTable->prepare_items($options);
            unset($options);

            $output['RdDownloadLogsListTable'] = $RdDownloadLogsListTable;
            unset($RdDownloadLogsListTable);

            $Loader = new \RdDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/Logs/pageIndex_v', $output);
            unset($Loader);
        }// pageIndex


        /**
         * Redirect to remove ugly querystring that is not necessary with the page process.
         */
        public function redirectNiceUrl()
        {
            $unwanted_querystring = ['_wpnonce', '_wp_http_referer', 'action', 'action2'];

            $current_query_names = array_map('strtolower', array_keys($_GET));

            foreach ($current_query_names as $name) {
                if (in_array($name, $unwanted_querystring)) {
                    unset($current_query_names);
                    wp_safe_redirect(remove_query_arg($unwanted_querystring));
                    exit();
                }
            }// endforeach;
            unset($name);

            unset($current_query_names, $unwanted_querystring);
        }// redirectNiceUrl


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_filter('set-screen-option', [$this, 'filterScreenOption'], 10, 3);
        }// registerHooks


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

            wp_enqueue_script('rd-download-logs-list-items', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/admin/Downloads/Logs/pageIndex.js', ['rd-downloads-common-functions'], RDDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rd-download-logs-list-items',
                'RdDownloads',
                [
                    'nonce' => wp_create_nonce('rd-downloads_ajax-manage-nonce'),
                    'txtAreYouSureDelete' => __('Are you sure?', 'rd-downloads') . "\n" . __('All the logs will be cleared.', 'rd-downloads') . "\n" . __('This can not be un-done.', 'rd-downloads'),
                ]
            );
        }// registerScripts


    }
}
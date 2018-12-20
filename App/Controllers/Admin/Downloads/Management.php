<?php
/**
 * Rundiz Downloads management page.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Downloads;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Downloads\\Management')) {
    class Management
    {


        /**
         * Add the screen options for management page.
         */
        public function addScreenOptions()
        {
            add_screen_option('per_page', [
                    'label' => __('Number of items per page:'),
                    'default' => 20,
                    'option' => 'rddownloads_items_perpage'// require alpha-numeric, underscore (_). no dash (-) allowed.
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
            $wp_upload_dir = wp_upload_dir();

            $output = [];
            $output['basedir'] = (isset($wp_upload_dir['basedir']) ? $wp_upload_dir['basedir'] : '');
            unset($wp_upload_dir);

            ob_start();
            $Loader->loadView('admin/Downloads/Management/helpTab/shortcodes_v', $output);
            $content = ob_get_contents();
            ob_end_clean();
            $screen->add_help_tab([
                'id' => 'rd-downloads-listing-helptab-shortcodes',
                'title' => __('Shortcodes', 'rd-downloads'),
                'content' => $content,
            ]);
            unset($content);

            ob_start();
            $Loader->loadView('admin/Downloads/Management/helpTab/permission_v', $output);
            $content = ob_get_contents();
            ob_end_clean();
            $screen->add_help_tab([
                'id' => 'rd-downloads-listing-helptab-permission',
                'title' => __('Permissions', 'rd-downloads'),
                'content' => $content,
            ]);
            unset($content);

            ob_start();
            $Loader->loadView('admin/Downloads/Management/helpTab/adminhelp_v');
            $content = ob_get_contents();
            ob_end_clean();
            $screen->add_help_tab([
                'id' => 'rd-downloads-listing-helptab-adminhelp',
                'title' => __('Admin help', 'rd-downloads'),
                'content' => $content,
            ]);
            unset($content);

            unset($Loader, $output);
        }// adminHelpTab


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
            if ($option === 'rddownloads_items_perpage') {
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
                wp_die(__('You do not have permission to access this page.'), '', ['response' => 403]);
            }

            // preset output value to views.
            $output = [];

            // initialize list table model.
            $RdDownloadsListTable = new \RdDownloads\App\Models\RdDownloadsListTable();
            $options = [];
            if (isset($_REQUEST['filter_user_id']) && trim($_REQUEST['filter_user_id']) != null) {
                $options['user_id'] = intval($_REQUEST['filter_user_id']);
            }
            if (isset($_REQUEST['filter_download_type']) && trim($_REQUEST['filter_download_type']) != null) {
                $options['download_type'] = trim($_REQUEST['filter_download_type']);
            }
            if (isset($_REQUEST['s']) && trim($_REQUEST['s']) != null) {
                // the s is forced by WordPress.
                $options['search'] = $_REQUEST['s'];
            }
            if (isset($_REQUEST['orderby'])) {
                // the orderby is forced by WordPress.
                $options['sort'] = $_REQUEST['orderby'];
            }
            if (isset($_REQUEST['order'])) {
                // the order is forced by WordPress.
                $options['order'] = $_REQUEST['order'];
            }
            $RdDownloadsListTable->prepare_items($options);
            unset($options);

            $output['RdDownloadsListTable'] = $RdDownloadsListTable;
            unset($RdDownloadsListTable);

            $Loader = new \RdDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/Management/pageIndex_v', $output);
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
                    wp_redirect(remove_query_arg($unwanted_querystring));
                    exit();
                }
            }// endforeach;
            unset($name);

            unset($current_query_names, $unwanted_querystring);
        }// redirectNiceUrl


        /**
         * Enqueue scripts here.
         */
        public function registerScripts()
        {
            wp_enqueue_script('rd-downloads-manage-form', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/admin/Downloads/Management/pageIndex.js', ['jquery', 'jquery-ui-core', 'rd-downloads-common-functions'], RDDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rd-downloads-manage-form',
                'RdDownloads',
                [
                    'nonce' => wp_create_nonce('rd-downloads_ajax-manage-nonce'),
                    'txtAreYouSureDelete' => __('Are you sure?', 'rd-downloads') . "\n" . __('The selected files will be deleted. GitHub and any remote files will be remain.', 'rd-downloads') . "\n" . __('This can not be un-done.', 'rd-downloads'),
                ]
            );
        }// registerScripts


        /**
         * Enqueue styles here.
         */
        public function registerStyles()
        {
            wp_enqueue_style('rd-downloads-font-awesome5');

            wp_enqueue_style('rd-downloads-manage-form', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/css/admin/Downloads/Management/pageIndex.css', [], RDDOWNLOADS_VERSION);
        }// registerStyles


    }
}
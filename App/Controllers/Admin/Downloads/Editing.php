<?php
/**
 * Rundiz Downloads editing page (including add, edit page).
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Downloads;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Downloads\\Editing')) {
    /**
     * Editing downloads class.
     */
    class Editing
    {


        use \RundizDownloads\App\AppTrait;


        /**
         * Class constructor.
         */
        public function __construct()
        {
            $this->getOptions();
        }// __construct


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
            $Loader->loadView('admin/Downloads/Editing/helpTab/tab1_v', $output);
            $content = ob_get_contents();
            unset($output);
            $screen->add_help_tab([
                'id' => 'rundiz-downloads-editing-helptab-1',
                'title' => __('Basic guide', 'rundiz-downloads'),
                'content' => $content,
            ]);
            unset($content);
            ob_clean();

            global $rundiz_downloads_options;
            $output['rundiz_downloads_options'] = $rundiz_downloads_options;
            $Loader->loadView('admin/Downloads/Editing/helpTab/tab2_v', $output);
            $content = ob_get_contents();
            $screen->add_help_tab([
                'id' => 'rundiz-downloads-editing-helptab-2',
                'title' => __('Force download', 'rundiz-downloads'),
                'content' => $content,
            ]);
            unset($content);

            if (ob_get_length() > 0) {
                ob_end_clean();
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
         * Add new download page.
         *
         * This method was called from Menu class.
         *
         * @global array $rundiz_downloads_options
         */
        public function pageAdd()
        {
            // check permission.
            if (!current_user_can('upload_files')) {
                wp_die(esc_html__('You do not have permission to access this page.', 'rundiz-downloads'), '', ['response' => 403]);
            }

            global $rundiz_downloads_options;

            $output = [];
            $output['page_heading1'] = __('Add new download', 'rundiz-downloads');
            $output['rundiz_downloads_options'] = $rundiz_downloads_options;

            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/Editing/pageEdit_v', $output);
            unset($Loader);
        }// pageAdd


        /**
         * Page edit downloads.
         *
         * This method was called from Menu class.
         *
         * @global array $rundiz_downloads_options
         */
        public function pageEdit()
        {
            // check permission.
            if (!current_user_can('upload_files')) {
                wp_die(esc_html__('You do not have permission to access this page.', 'rundiz-downloads'), '', ['response' => 403]);
            }

            global $rundiz_downloads_options;

            $output = [];
            $output['page_heading1'] = __('Edit download', 'rundiz-downloads');
            $output['rundiz_downloads_options'] = $rundiz_downloads_options;

            // prepare data for form.
            $RdDownloads = new \RundizDownloads\App\Models\RdDownloads();
            $rdDownloadsData = $RdDownloads->get(
                [
                    'download_id' => filter_input(INPUT_GET, 'download_id', FILTER_SANITIZE_NUMBER_INT),
                ]
            );
            unset($RdDownloads);

            if (empty($rdDownloadsData) || is_null($rdDownloadsData)) {
                wp_die(esc_html__('The editing item was not found.', 'rundiz-downloads'), '', ['response' => 404]);
            } elseif (
                isset($rdDownloadsData->user_id) && 
                intval($rdDownloadsData->user_id) !== get_current_user_id() && 
                !current_user_can('edit_others_posts')
            ) {
                // if this user is not editing own downloads data and do not have permission to edit other's posts.
                wp_die(esc_html__('You do not have permission to edit other\'s downloads data.', 'rundiz-downloads'), '', ['response' => 403]);
            }

            if (is_object($rdDownloadsData)) {
                foreach ($rdDownloadsData as $name => $value) {
                    $output[$name] = $value;
                }// endforeach;
                unset($name, $value);

                // extract options
                if (isset($rdDownloadsData->download_options)) {
                    $download_options = maybe_unserialize($rdDownloadsData->download_options);
                    foreach ($download_options as $name => $value) {
                        $output[$name] = $value;
                    }// endforeach;
                    unset($name, $value);
                    unset($download_options);
                }
            }

            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/Editing/pageEdit_v', $output);
            unset($Loader);
        }// pageEdit


        /**
         * Enqueue scripts here.
         */
        public function registerScripts()
        {
            wp_enqueue_script('rundiz-downloads-edit-form-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/Admin/Downloads/Management/pageEdit.js', ['jquery', 'wp-util', 'rundiz-downloads-common-functions-js'], RUNDIZDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rundiz-downloads-edit-form-js',
                'RdDownloads',
                [
                    'nonce' => wp_create_nonce('rundiz-downloads_ajax-file-browser-nonce'),
                    'savenonce' => wp_create_nonce('rundiz-downloads-ajax-saving-nonce'),
                    'txtAreYouSureDeleteFileUndone' => __('Are you sure?', 'rundiz-downloads') . "\n" . __('If the selected file had linked with other downloads then it will show the error message.', 'rundiz-downloads') . "\n" . __('This can not be un-done.', 'rundiz-downloads'),
                    'txtDismiss' => __('Dismiss', 'rundiz-downloads'),
                ]
            );
        }// registerScripts


        /**
         * Enqueue styles here.
         */
        public function registerStyles()
        {
            wp_enqueue_style('rundiz-downloads-font-awesome5');

            wp_enqueue_style('rundiz-downloads-edit-form-css', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/css/Admin/Downloads/Management/pageEdit.css', [], RUNDIZDOWNLOADS_VERSION);
        }// registerStyles


    }// Editing
}

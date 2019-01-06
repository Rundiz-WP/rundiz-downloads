<?php
/**
 * Rundiz Downloads editing page (including add, edit page).
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Downloads;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Downloads\\Editing')) {
    class Editing
    {


        use \RdDownloads\App\AppTrait;


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
            $Loader = new \RdDownloads\App\Libraries\Loader();
            $wp_upload_dir = wp_upload_dir();

            $output = [];
            $output['basedir'] = (isset($wp_upload_dir['basedir']) ? $wp_upload_dir['basedir'] : '');
            unset($wp_upload_dir);

            ob_start();
            $Loader->loadView('admin/Downloads/Editing/helpTab/tab1_v', $output);
            $content = ob_get_contents();
            unset($output);
            $screen->add_help_tab([
                'id' => 'rd-downloads-editing-helptab-1',
                'title' => __('Basic guide', 'rd-downloads'),
                'content' => $content,
            ]);
            unset($content);
            ob_clean();

            global $rd_downloads_options;
            $output['rd_downloads_options'] = $rd_downloads_options;
            $Loader->loadView('admin/Downloads/Editing/helpTab/tab2_v', $output);
            $content = ob_get_contents();
            $screen->add_help_tab([
                'id' => 'rd-downloads-editing-helptab-2',
                'title' => __('Force download', 'rd-downloads'),
                'content' => $content,
            ]);
            unset($content);

            if (ob_get_length() > 0) {
                ob_end_clean();
            }
            unset($Loader);
        }// adminHelpTab


        /**
         * Add new download page.
         *
         * This method was called from Menu class.
         *
         * @global array $rd_downloads_options
         */
        public function pageAdd()
        {
            // check permission.
            if (!current_user_can('upload_files')) {
                wp_die(__('You do not have permission to access this page.'), '', ['response' => 403]);
            }

            global $rd_downloads_options;

            $output = [];
            $output['page_heading1'] = __('Add new download', 'rd-downloads');
            $output['rd_downloads_options'] = $rd_downloads_options;

            $Loader = new \RdDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/Editing/pageEdit_v', $output);
            unset($Loader);
        }// pageAdd


        /**
         * Page edit downloads.
         *
         * This method was called from Menu class.
         *
         * @global array $rd_downloads_options
         */
        public function pageEdit()
        {
            // check permission.
            if (!current_user_can('upload_files')) {
                wp_die(__('You do not have permission to access this page.'), '', ['response' => 403]);
            }

            global $rd_downloads_options;

            $output = [];
            $output['page_heading1'] = __('Edit download', 'rd-downloads');
            $output['rd_downloads_options'] = $rd_downloads_options;

            // prepare data for form.
            $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
            $rdDownloadsData = $RdDownloads->get(
                [
                    'download_id' => filter_input(INPUT_GET, 'download_id', FILTER_SANITIZE_NUMBER_INT),
                ]
            );
            unset($RdDownloads);

            if (empty($rdDownloadsData) || is_null($rdDownloadsData)) {
                wp_die(__('The editing item was not found.', 'rd-downloads'), '', ['response' => 404]);
            } elseif (isset($rdDownloadsData->user_id) && $rdDownloadsData->user_id != get_current_user_id() && !current_user_can('edit_others_posts')) {
                // if this user is not editing own downloads data and do not have permission to edit other's posts.
                wp_die(__('You do not have permission to edit other\'s downloads data.', 'rd-downloads'), '', ['response' => 403]);
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

            $Loader = new \RdDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/Downloads/Editing/pageEdit_v', $output);
            unset($Loader);
        }// pageEdit


        /**
         * Enqueue scripts here.
         */
        public function registerScripts()
        {
            global $rd_downloads_options;

            wp_enqueue_script('rd-downloads-edit-form', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/admin/Downloads/Management/pageEdit.js', ['jquery', 'wp-util', 'rd-downloads-common-functions'], RDDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rd-downloads-edit-form',
                'RdDownloads',
                [
                    'nonce' => wp_create_nonce('rd-downloads_ajax-file-browser-nonce'),
                    'savenonce' => wp_create_nonce('rd-downloads-ajax-saving-nonce'),
                    'txtAreYouSureDeleteFileUndone' => __('Are you sure?', 'rd-downloads') . "\n" . __('If the selected file had linked with other downloads then it will show the error message.', 'rd-downloads') . "\n" . __('This can not be un-done.', 'rd-downloads'),
                    'txtDismiss' => __('Dismiss', 'rd-downloads'),
                ]
            );
        }// registerScripts


        /**
         * Enqueue styles here.
         */
        public function registerStyles()
        {
            wp_enqueue_style('rd-downloads-font-awesome5');

            wp_enqueue_style('rd-downloads-edit-form', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/css/admin/Downloads/Management/pageEdit.css', [], RDDOWNLOADS_VERSION);
        }// registerStyles


    }
}
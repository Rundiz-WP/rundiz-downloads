<?php
/**
 * Add download button into content editor hook.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Hooks\ClassicEditor;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Hooks\\ClassicEditor\\DownloadButton')) {
    /**
     * Add a download button class.
     * 
     * This will add a button to add a download shortcode into post content.
     * 
     * @link https://developer.wordpress.org/reference/hooks/media_buttons/ Reference.
     * @link https://developer.wordpress.org/reference/hooks/wp_enqueue_media/ Reference.
     * @link https://www.sitepoint.com/adding-a-media-button-to-the-content-editor/ Example code.
     * @link https://gist.github.com/vralle/9e28e9d18a4b340b93ad Example code.
     */
    class DownloadButton implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Modify TinyMCE dependencies.
         */
        public function modifyTinymceDependencies()
        {
            $WpScripts = wp_scripts();
            if (isset($WpScripts->registered['wp-tinymce'])) {
                array_push($WpScripts->registered['wp-tinymce']->deps, 'rd-downloads-tinymce-ajax');
            }
        }// modifyTinymceDependencies


        /**
         * Register TinyMce buttons
         * 
         * @param array $buttons
         * @return array
         */
        public function registerButtons($buttons)
        {
            if (isset($buttons[11])) {
                // if $button key 11 (position 12) exists, add a button after this.
                array_splice($buttons, 12, 0, 'rddownloads_button');
            } else {
                array_push($buttons, 'separator', 'rddownloads_button');
            }
            return $buttons;
        }// registerButtons


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('admin_enqueue_scripts', [$this, 'registerStyles']);
            add_action('admin_enqueue_scripts', [$this, 'registerScripts']);
            add_action('admin_enqueue_scripts', [$this, 'modifyTinymceDependencies']);

            add_filter('mce_buttons', [$this, 'registerButtons']);
            add_filter('mce_external_plugins', [$this, 'registerTinyMceJavascript']);
        }// registerHooks


        /**
         * Enqueue scripts
         * 
         * @param string $hook
         */
        public function registerScripts($hook)
        {
            
            if ($hook == 'post.php' || $hook == 'post-new.php') {
                $Loader = new \RdDownloads\App\Libraries\Loader();
                ob_start();
                $Loader->loadView('admin/Hooks/ClassicEditor/downloadTinyMCEBrowser_v');
                $dialogContent = ob_get_contents();
                ob_end_clean();

                wp_enqueue_script('rd-downloads-tinymce-ajax', plugins_url('/assets/js/admin/Hooks/ClassicEditor/tinymce-ajax.js', RDDOWNLOADS_FILE), ['jquery', 'rd-downloads-common-functions', 'wp-util'], RDDOWNLOADS_VERSION, true);
                wp_localize_script(
                    'rd-downloads-tinymce-ajax',
                    'RdDownloads',
                    [
                        'nonce' => wp_create_nonce('rd-downloads_editor-ajax-nonce'),
                        'customDialogContent' => $dialogContent,
                        'txtAddADownload' => __('Add a download', 'rd-downloads'),
                        'txtAnyRemoteFile' => __('Any remote file', 'rd-downloads'),
                        'txtGitHubFile' => __('GitHub file', 'rd-downloads'),
                        'txtLocalFile' => __('Local file', 'rd-downloads'),
                        'txtUnknow' => __('Unknown', 'rd-downloads'),
                    ]
                );
                unset($dialogContent, $Loader);
            }
        }// registerScripts


        /**
         * Enqueue styles.
         * 
         * @param string $hook
         */
        public function registerStyles($hook)
        {
            if ($hook == 'post.php' || $hook == 'post-new.php') {
                if (!wp_script_is('rd-downloads-font-awesome5', 'registered')) {
                    $StylesScripts = new \RdDownloads\App\Libraries\StylesAndScripts();
                    $StylesScripts->registerStylesAndScripts();
                    unset($StylesScripts);
                }
                wp_enqueue_style('rd-downloads-font-awesome5');
                wp_enqueue_style('rd-downloads-tinymce-font-awesome5', plugin_dir_url(RDDOWNLOADS_FILE).'assets/css/admin/Hooks/ClassicEditor/tinymce-font-awesome5.css');
                wp_enqueue_style('rd-downloads-tinymce-custom-dialog', plugin_dir_url(RDDOWNLOADS_FILE).'assets/css/admin/Hooks/ClassicEditor/tinymce-custom-dialog.css');
            }
        }// registerStyles


        /**
         * Register TinyMCE JS for new button.
         * 
         * @param array $plugin_array
         * @return array
         */
        public function registerTinyMceJavascript($plugin_array)
        {
            $plugin_array['rddownloads_button'] =  plugins_url('/assets/js/admin/Hooks/ClassicEditor/tinymce-dialog.js', RDDOWNLOADS_FILE);
            return $plugin_array;
        }// registerTinyMceJavascript


    }
}
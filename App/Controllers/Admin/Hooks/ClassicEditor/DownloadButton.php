<?php
/**
 * Add download button into content editor hook.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Hooks\ClassicEditor;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Hooks\\ClassicEditor\\DownloadButton')) {
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
    class DownloadButton implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Modify TinyMCE dependencies.
         */
        public function modifyTinymceDependencies()
        {
            $WpScripts = wp_scripts();
            if (isset($WpScripts->registered['wp-tinymce'])) {
                array_push($WpScripts->registered['wp-tinymce']->deps, 'rundiz-downloads-tinymce-ajax');
            }
        }// modifyTinymceDependencies


        /**
         * Register TinyMce buttons
         * 
         * @param array $buttons Buttons.
         * @return array
         */
        public function registerButtons($buttons)
        {
            global $pagenow;
            if (('post.php' === $pagenow || 'post-new.php' === $pagenow) && is_admin()) {
                if (isset($buttons[11])) {
                    // if $button key 11 (position 12) exists, add a button after this.
                    array_splice($buttons, 12, 0, 'rundiz_downloads_button');
                } else {
                    array_push($buttons, 'separator', 'rundiz_downloads_button');
                }
            }
            return $buttons;
        }// registerButtons


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('init', [$this, 'registerCommonScripts']);
            add_action('admin_enqueue_scripts', [$this, 'registerStyles']);
            add_action('admin_enqueue_scripts', [$this, 'registerScripts']);
            add_action('admin_enqueue_scripts', [$this, 'modifyTinymceDependencies']);

            add_filter('mce_buttons', [$this, 'registerButtons']);
            add_filter('mce_external_plugins', [$this, 'registerTinyMceJavascript']);
        }// registerHooks


        /**
         * Register common scripts.
         */
        public function registerCommonScripts()
        {
            wp_register_script(
                'rundiz-downloads-tinymce-ajax', 
                plugins_url('/assets/js/admin/Hooks/ClassicEditor/tinymce-ajax.js', RUNDIZDOWNLOADS_FILE), 
                [
                    'jquery', 
                    'rundiz-downloads-common-functions-js', 
                    'wp-util',
                ], 
                RUNDIZDOWNLOADS_VERSION, 
                true
            );
        }// registerCommonScripts


        /**
         * Enqueue scripts
         * 
         * @param string $hook Hook name.
         */
        public function registerScripts($hook)
        {
            
            if ('post.php' === $hook || 'post-new.php' === $hook) {
                $Loader = new \RundizDownloads\App\Libraries\Loader();
                ob_start();
                $Loader->loadView('admin/Hooks/ClassicEditor/downloadTinyMCEBrowser_v');
                $dialogContent = ob_get_contents();
                ob_end_clean();

                wp_enqueue_script('rundiz-downloads-tinymce-ajax');
                wp_localize_script(
                    'rundiz-downloads-tinymce-ajax',
                    'RdDownloads',
                    [
                        'nonce' => wp_create_nonce('rundiz-downloads_editor-ajax-nonce'),
                        'customDialogContent' => $dialogContent,
                        'txtAddADownload' => __('Add a download', 'rundiz-downloads'),
                        'txtAnyRemoteFile' => __('Any remote file', 'rundiz-downloads'),
                        'txtGitHubFile' => __('GitHub file', 'rundiz-downloads'),
                        'txtLocalFile' => __('Local file', 'rundiz-downloads'),
                        'txtUnknow' => __('Unknown', 'rundiz-downloads'),
                    ]
                );
                unset($dialogContent, $Loader);
            }
        }// registerScripts


        /**
         * Enqueue styles.
         * 
         * @param string $hook Hook name.
         */
        public function registerStyles($hook)
        {
            if ('post.php' === $hook || 'post-new.php' === $hook) {
                if (!wp_script_is('rundiz-downloads-font-awesome5', 'registered')) {
                    $StylesScripts = new \RundizDownloads\App\Libraries\StylesAndScripts();
                    $StylesScripts->registerStylesAndScripts();
                    unset($StylesScripts);
                }
                wp_enqueue_style('rundiz-downloads-font-awesome5');
                wp_enqueue_style('rundiz-downloads-tinymce-font-awesome5', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/css/admin/Hooks/ClassicEditor/tinymce-font-awesome5.css', [], RUNDIZDOWNLOADS_VERSION);
                wp_enqueue_style('rundiz-downloads-tinymce-custom-dialog', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/css/admin/Hooks/ClassicEditor/tinymce-custom-dialog.css', [], RUNDIZDOWNLOADS_VERSION);
            }
        }// registerStyles


        /**
         * Register TinyMCE JS for new button.
         * 
         * @param array $plugin_array Plugin array.
         * @return array
         */
        public function registerTinyMceJavascript($plugin_array)
        {
            global $pagenow;
            if (('post.php' === $pagenow || 'post-new.php' === $pagenow) && is_admin()) {
                $plugin_array['rundiz_downloads_button'] = plugins_url('/assets/js/admin/Hooks/ClassicEditor/tinymce-dialog.js', RUNDIZDOWNLOADS_FILE);
            }
            return $plugin_array;
        }// registerTinyMceJavascript


    }// DownloadButton
}

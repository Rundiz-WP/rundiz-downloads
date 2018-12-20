<?php
/**
 * Add [rddownloads] shortcode help tab to editor page.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Hooks\EditorHelptab;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Hooks\\EditorHelptab\\rdDownloadsShortcode')) {
    class rdDownloadsShortcode implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Display download shortcode help tab.
         */
        public function rdDownloadsShortcodeHelpTab()
        {
            $Loader = new \RdDownloads\App\Libraries\Loader();
            $screen = get_current_screen();

            if (
                $screen instanceof \WP_Screen && 
                in_array(strtolower($screen->id), ['edit-page', 'page', 'edit-post', 'post'])
            ) {
                // if in post, add post, edit post, page, add page, edit page.
                ob_start();
                $Loader->loadView('admin/Downloads/Management/helpTab/shortcodes_v');
                $content = ob_get_contents();
                ob_end_clean();
                // add the help tab content to the last.
                $screen->add_help_tab([
                    'id' => 'rd-downloads-listing-helptab-shortcodes',
                    'title' => __('Rundiz Downloads shortcodes', 'rd-downloads'),
                    'content' => $content,
                ]);
                unset($content);
            }// endif;

            unset($Loader);
        }// rdDownloadsShortcodeHelpTab


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('in_admin_header', [$this, 'rdDownloadsShortcodeHelpTab']);
        }// registerHooks


    }
}
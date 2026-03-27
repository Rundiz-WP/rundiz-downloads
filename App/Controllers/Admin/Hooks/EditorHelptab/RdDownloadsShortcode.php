<?php
/**
 * Add [rddownloads] shortcode help tab to editor page.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Hooks\EditorHelptab;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Hooks\\EditorHelptab\\RdDownloadsShortcode')) {
    /**
     * `rddownloads` short code class.
     */
    class RdDownloadsShortcode implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Display download short code help tab.
         */
        public function rdDownloadsShortcodeHelpTab()
        {
            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $screen = get_current_screen();

            if (
                $screen instanceof \WP_Screen && 
                in_array(strtolower($screen->id), ['edit-page', 'page', 'edit-post', 'post'], true)
            ) {
                // if in post, add post, edit post, page, add page, edit page.
                ob_start();
                $Loader->loadView('admin/Downloads/Management/helpTab/shortcodes_v');
                $content = ob_get_contents();
                ob_end_clean();
                // add the help tab content to the last.
                $screen->add_help_tab([
                    'id' => 'rundiz-downloads-listing-helptab-shortcodes',
                    'title' => __('Rundiz Downloads shortcodes', 'rundiz-downloads'),
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


    }// rdDownloadsShortcode
}

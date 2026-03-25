<?php
/**
 * Rundiz Downloads admin menu.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Downloads;

if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Downloads\\Menu')) {
    class Menu implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * @var string Rundiz downloads main page slug. This constant must be public.
         */
        const MENU_SLUG = 'rd-downloads';


        /**
         * Manually highlight parent menu.
         *
         * This is very useful on sub pages that is not listing in admin menu/sub menu. It will modify $plugin_page by conditions.
         *
         * @link https://stackoverflow.com/a/28856349/128761 Source code copied from here.
         * @global string $plugin_page
         * @param string $parent_file
         * @return string
         */
        public function manualHighlightParentMenu($parent_file)
        {
            global $plugin_page;

            if ('rd-downloads_edit' === $plugin_page) {
                // if in rundiz downloads/edit page.
                // modify $plugin_page as parent menu ('rd-downloads');
                // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                $plugin_page = self::MENU_SLUG;
            }

            return $parent_file;
        }// manualHighlightParentMenu


        /**
         * {@inheritDoc}
         */
        public function registerHooks() {
            if (is_admin()) {
                add_action('admin_menu', [$this, 'rdDownloadsMenu']);

                add_filter('parent_file', [$this, 'manualHighlightParentMenu']);

                $Management = new Management();
                add_filter('set-screen-option', [$Management, 'filterScreenOption'], 11, 3);
                unset($Management);
            }
        }// registerHooks


        /**
         * Downloads admin menu.
         *
         * This will be call to the manage/add/edit pages.
         */
        public function rdDownloadsMenu() {
            $Management = new Management();
            $hook_suffix = add_menu_page(__('Rundiz Downloads', 'rundiz-downloads'), __('Downloads', 'rundiz-downloads'), 'edit_posts', self::MENU_SLUG, [$Management, 'pageIndex'], 'dashicons-download', 26);
            $Management->hook_suffix = $hook_suffix;
            if (is_string($hook_suffix)) {
                add_action('load-' . $hook_suffix, [$Management, 'redirectNiceUrl']);
                add_action('load-' . $hook_suffix, [$Management, 'addScreenOptions']);
                add_action('load-' . $hook_suffix, [$Management, 'adminHelpTab']);
                add_action('load-' . $hook_suffix, [$Management, 'callEnqueueHook']);
            }
            unset($hook_suffix, $Management);

            // editing pages ---------------------------------------------------------------------------------------------------
            // add page.
            $Editing = new Editing();
            $hook_suffix = add_submenu_page(self::MENU_SLUG, __('Add new download', 'rundiz-downloads'), __('Add New', 'rundiz-downloads'), 'upload_files', 'rd-downloads_add', [$Editing, 'pageAdd']);
            if (is_string($hook_suffix)) {
                add_action('load-' . $hook_suffix, [$Editing, 'adminHelpTab']);
                add_action('load-' . $hook_suffix, [$Editing, 'callEnqueueHook']);
            }
            unset($hook_suffix);

            // edit page.
            // set random name to parent slug argument to prevent it displaying in submenu. ( https://stackoverflow.com/a/11820396/128761 )
            $hook_suffix = add_submenu_page('rd-downloads_NOTEXISTS', __('Edit download', 'rundiz-downloads'), null, 'upload_files', 'rd-downloads_edit', [$Editing, 'pageEdit']);
            if (is_string($hook_suffix)) {
                add_action('load-' . $hook_suffix, [$Editing, 'adminHelpTab']);
                add_action('load-' . $hook_suffix, [$Editing, 'callEnqueueHook']);
            }
            unset($hook_suffix, $Editing);
            // end editing pages ----------------------------------------------------------------------------------------------

            // download logs page.
            $Logs = new Logs();
            $Logs->downloadLogsMenu();
            unset($Logs);

            // GitHub OAuth (connect) page.
            $GithubOAuth = new GithubOAuth();
            $GithubOAuth->githubOAuthMenu();
            unset($GithubOAuth);
        }// rdDownloadsMenu


    }
}
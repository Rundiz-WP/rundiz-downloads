<?php
/**
 * Add settings sub menu and page into the Settings menu.
 *
 * Last update: 2026-03-27
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin;


if (!defined('ABSPATH')) {
    exit();
}


use RundizDownloads\App\Libraries\FileSystem;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Settings')) {
    /**
     * Admin settings page.
     */
    class Settings implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        use \RundizDownloads\App\AppTrait;


        /**
         * @var string This menu slug. This constant must be public.
         */
        const MENU_SLUG = 'rundiz-downloads_settings';


        /**
         * @var string The current admin page.
         */
        private $hookSuffix = '';


        /**
         * Allow code/WordPress to call hook `admin_enqueue_scripts` 
         * then `wp_register_script()`, `wp_localize_script()`, `wp_enqueue_script()` functions will be working fine later.
         * 
         * @link https://wordpress.stackexchange.com/a/76420/41315 Original source code.
         * @since 2025-10-14
         */
        public function callEnqueueHook()
        {
            add_action('admin_enqueue_scripts', [$this, 'registerScripts']);
        }// callEnqueueHook


        /**
         * An example of how to access settings variable and its values.
         *
         * @global array $rundiz_downloads_options
         */
        public function pluginReadSettingsPage()
        {
            $this->getOptions();
            global $rundiz_downloads_options;

            $output = [];
            $output['rundiz_downloads_options'] = $rundiz_downloads_options;

            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/readsettings_v', $output);
            unset($Loader, $output);
        }// pluginReadSettingsPage


        /**
         * The plugin settings sub menu to go to settings page.
         */
        public function pluginSettingsMenu()
        {
            $hook_suffix = add_submenu_page(Downloads\Menu::MENU_SLUG, __('Downloads Settings', 'rundiz-downloads'), __('Settings', 'rundiz-downloads'), 'manage_options', self::MENU_SLUG, [$this, 'pluginSettingsPage']);
            if (is_string($hook_suffix)) {
                $this->hookSuffix = $hook_suffix;
                add_action('load-' . $hook_suffix, [$this, 'callEnqueueHook']);
            }
            unset($hook_suffix);

            //add_options_page(__('Rundiz Downloads read settings value', 'rundiz-downloads'), __('Rundiz Downloads read settings', 'rundiz-downloads'), 'manage_options', 'rundiz-downloads-read-settings', [$this, 'pluginReadSettingsPage']);
        }// pluginSettingsMenu


        /**
         * Display plugin settings page.
         *
         * @global \wpdb $wpdb
         */
        public function pluginSettingsPage()
        {
            // check permission.
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have permission to access this page.', 'rundiz-downloads'));
            }

            if (get_transient('rundiz_downloads_transient__updated')) {
                if (current_user_can('update_plugins')) {
                    wp_die(
                        sprintf(
                            // translators: %1$s Open link, %2$s Close link.
                            esc_html__('The manual update is required, please %1$supdate first%2$s.', 'rundiz-downloads'), 
                            '<a href="' . esc_url(network_admin_url('index.php?page=' . Plugins\Upgrader::MENU_SLUG)) . '">', 
                            '</a>'
                        )
                    );
                } else {
                    wp_die(
                        esc_html__('The manual update is required, please tell administrator to update first.', 'rundiz-downloads')
                    );
                }
            }

            // load config values to get settings config file.
            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $config_values = $Loader->loadConfig();
            if (is_array($config_values) && array_key_exists('rundiz_settings_config_file', $config_values)) {
                $settings_config_file = $config_values['rundiz_settings_config_file'];
            } else {
                wp_die(esc_html__('Settings configuration file was not set.', 'rundiz-downloads'));
                exit(1);
            }
            unset($config_values);

            $RundizSettings = new \RundizDownloads\App\Libraries\RundizSettings();
            $RundizSettings->settings_config_file = $settings_config_file;

            $options_values = $this->getOptions();
            $output = [];

            // if form submitted
            if (isset($_POST) && !empty($_POST)) {
                $wpnonce = '';
                if (isset($_POST['_wpnonce'])) {
                    $wpnonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
                }

                if (!wp_verify_nonce($wpnonce)) {
                    wp_nonce_ays('-1');
                }
                unset($wpnonce);

                // populate form field values.
                $options_values = $RundizSettings->getSubmittedData();

                // you may validate form here first.

                // if admin cleanup client id and secret then remove all the access token from users.
                if ('' === $options_values['rdd_github_client_id'] && '' === $options_values['rdd_github_client_secret']) {
                    global $wpdb;
                    $Github = new \RundizDownloads\App\Libraries\Github();
                    $wpdb->query($wpdb->prepare('UPDATE `' . $wpdb->usermeta . '` SET `meta_value`=\'\' WHERE `meta_key` = %s', [$Github->getOAuthAccessTokenName()]));// phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    unset($Github);
                }

                // create necessary file on save.
                $FileSystem = new \RundizDownloads\App\Libraries\FileSystem();
                $wp_upload_dir = wp_upload_dir();
                if (
                    is_array($wp_upload_dir) &&
                    array_key_exists('basedir', $wp_upload_dir)
                ) {
                    if (isset($options_values['rdd_force_download']) && strval($options_values['rdd_force_download']) === '1') {
                        // if setting is to Force download, create .htaccess to prevent direct access file.
                        $FileSystem->writeFile(trailingslashit($wp_upload_dir['basedir']) . FileSystem::UPLOAD_FOLDER_NAME . '/.htaccess', 'Options -Indexes' . PHP_EOL . 'deny from all', false);
                    } else {
                        // if setting is NOT to Force download, remove .htaccess to allow direct access file.
                        $FileSystem->deleteFile(trailingslashit($wp_upload_dir['basedir']) . FileSystem::UPLOAD_FOLDER_NAME . '/.htaccess');
                    }
                }
                unset($FileSystem, $wp_upload_dir);

                // then save data.
                $output['save_result'] = $this->saveOptions($options_values);

                $output['form_result_class'] = 'notice-success';
                $output['form_result_msg'] = __('Settings saved.', 'rundiz-downloads');

                // clear all cache on save.
                $Cache = new \RundizDownloads\App\Libraries\Cache();
                $output['cacheCleared'] = $Cache->getInstance()->clear();
                unset($Cache);
            }// endif $_POST

            $output['settings_page'] = $RundizSettings->getSettingsPage($options_values);
            unset($RundizSettings, $options_values);

            $Loader->loadView('admin/settings_v', $output);
            unset($Loader, $output);
        }// pluginSettingsPage


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('admin_menu', [$this, 'pluginSettingsMenu']);
        }// registerHooks


        /**
         * Enqueue scripts and styles here.
         * 
         * @param string $hook_suffix The current admin page.
         */
        public function registerScripts($hook_suffix = '')
        {
            if ($hook_suffix !== $this->hookSuffix) {
                return;
            }

            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $config_values = $Loader->loadConfig();
            if (is_array($config_values) && array_key_exists('rundiz_settings_config_file', $config_values)) {
                $settings_config_file = $config_values['rundiz_settings_config_file'];
                $RundizSettings = new \RundizDownloads\App\Libraries\RundizSettings();
                $RundizSettings->settings_config_file = $settings_config_file;
                $hasEditorField = $RundizSettings->hasEditor();
                $hasMediaField = $RundizSettings->hasMedia();
                unset($RundizSettings, $settings_config_file);
            }
            unset($config_values, $Loader);

            if (isset($hasEditorField) && true === $hasEditorField) {
                wp_enqueue_editor();
                wp_enqueue_media();
            }
            unset($hasEditorField);
            if (isset($hasMediaField) && true === $hasMediaField) {
                wp_enqueue_script('jquery');
                wp_enqueue_media();
            }
            unset($hasMediaField);

            wp_enqueue_style('rundiz-downloads-font-awesome5');

            wp_enqueue_style('rundiz-downloads-settings-tabs-css');
            wp_enqueue_script('rundiz-downloads-settings-tabs-js');

            // custom ajax for settings page.
            wp_enqueue_script('rundiz-downloads-settings-ajax-js', plugin_dir_url(RUNDIZDOWNLOADS_FILE) . 'assets/js/Admin/settings/settings-ajax.js', [], RUNDIZDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rundiz-downloads-settings-ajax-js',
                'RdDownloadsSettings',
                [
                    'nonce' => wp_create_nonce('rundiz-downloads-settings_ajax-settings-nonce'),
                ]
            );

            // you can remove some or all of the line below if you don't use it. ---------
            // css & js for code editor.
            wp_enqueue_style('rundiz-downloads-settings-ace-editor-css');
            wp_enqueue_script('rundiz-downloads-ace-editor-js');
            wp_enqueue_script('rundiz-downloads-settings-ace-editor-js');
        }// registerScripts


    }// Settings
}

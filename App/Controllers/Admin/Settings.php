<?php
/**
 * Add settings sub menu and page into the Settings menu.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Settings')) {
    class Settings implements \RdDownloads\App\Controllers\ControllerInterface
    {


        use \RdDownloads\App\AppTrait;


        /**
         * An example of how to access settings variable and its values.
         *
         * @global array $rd_downloads_options
         */
        public function pluginReadSettingsPage()
        {
            $this->getOptions();
            global $rd_downloads_options;

            $output = [];
            $output['rd_downloads_options'] = $rd_downloads_options;

            $Loader = new \RdDownloads\App\Libraries\Loader();
            $Loader->loadView('admin/readsettings_v', $output);
            unset($Loader, $output);
        }// pluginReadSettingsPage


        /**
         * The plugin settings sub menu to go to settings page.
         */
        public function pluginSettingsMenu()
        {
            $hook_suffix = add_submenu_page('rd-downloads', __('Downloads Settings', 'rd-downloads'), __('Settings', 'rd-downloads'), 'manage_options', 'rd-downloads_settings', [$this, 'pluginSettingsPage']);
            add_action('admin_print_styles-' . $hook_suffix, [$this, 'registerScripts']);// no longer use load-$hook_suffix because it will not working with register scripts and styles.
            add_action('admin_print_scripts-' . $hook_suffix, [$this, 'registerScripts']);// no longer use load-$hook_suffix because it will not working with register scripts and styles.
            unset($hook_suffix);

            //add_options_page(__('Plugin Template read settings value', 'rd-downloads'), __('Plugin Template read settings', 'rd-downloads'), 'manage_options', 'rd-downloads-read-settings', [$this, 'pluginReadSettingsPage']);
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
                wp_die(__('You do not have permission to access this page.'));
            }

            if (get_transient('rd_downloads_updated')) {
                if (current_user_can('update_plugins')) {
                    /* translators: %1$s: Open link tag, %2$s: Close link tag. */
                    wp_die(sprintf(__('The manual update is required, please %1$supdate first%2$s.', 'rd-downloads'), '<a href="' . esc_attr(network_admin_url('index.php?page=rd-downloads-manual-update')) . '">', '</a>'));
                } else {
                    wp_die(__('The manual update is required, please tell administrator to update first.', 'rd-downloads'));
                }
            }

            // load config values to get settings config file.
            $Loader = new \RdDownloads\App\Libraries\Loader();
            $config_values = $Loader->loadConfig();
            if (is_array($config_values) && array_key_exists('rundiz_settings_config_file', $config_values)) {
                $settings_config_file = $config_values['rundiz_settings_config_file'];
            } else {
                echo 'Settings configuration file was not set.';
                die('Settings configuration file was not set.');
                exit;
            }
            unset($config_values);

            $RundizSettings = new \RdDownloads\App\Libraries\RundizSettings();
            $RundizSettings->settings_config_file = $settings_config_file;

            $options_values = $this->getOptions();
            $output = [];

            // if form submitted
            if (isset($_POST) && !empty($_POST)) {
                if (!wp_verify_nonce((isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : ''))) {
                    wp_nonce_ays('-1');
                }

                // populate form field values.
                $options_values = $RundizSettings->getSubmittedData();

                // you may validate form here first.

                // if admin cleanup client id and secret then remove all the access token from users.
                if ($options_values['rdd_github_client_id'] == '' && $options_values['rdd_github_client_secret'] == '') {
                    global $wpdb;
                    $Github = new \RdDownloads\App\Libraries\Github();
                    $wpdb->query($wpdb->prepare('UPDATE `' . $wpdb->usermeta . '` SET `meta_value`=\'\' WHERE `meta_key` = %s', [$Github->getOAuthAccessTokenName()]));
                    unset($Github);
                }

                // create necessary file on save.
                $FileSystem = new \RdDownloads\App\Libraries\FileSystem();
                $wp_upload_dir = wp_upload_dir();
                if (
                    is_array($wp_upload_dir) &&
                    array_key_exists('basedir', $wp_upload_dir)
                ) {
                    if (isset($options_values['rdd_force_download']) && $options_values['rdd_force_download'] == '1') {
                        // if setting is to Force download, create .htaccess to prevent direct access file.
                        $FileSystem->writeFile(trailingslashit($wp_upload_dir['basedir']) . 'rd-downloads/.htaccess', 'Options -Indexes' . PHP_EOL . 'deny from all', false);
                    } else {
                        // if setting is NOT to Force download, remove .htaccess to allow direct access file.
                        $FileSystem->deleteFile(trailingslashit($wp_upload_dir['basedir']) . 'rd-downloads/.htaccess');
                    }
                }
                unset($FileSystem, $wp_upload_dir);

                // then save data.
                $result = $this->saveOptions($options_values);

                if ($result === true) {
                    $output['form_result_class'] = 'notice-success';
                    $output['form_result_msg'] = __('Settings saved.');
                } else {
                    $output['form_result_class'] = 'notice-success';
                    $output['form_result_msg'] =  __('Settings saved.');
                }

                // clear all cache on save.
                $Cache = new \RdDownloads\App\Libraries\Cache();
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
            if (is_admin()) {
                add_action('admin_menu', [$this, 'pluginSettingsMenu']);
            }
        }// registerHooks


        /**
         * Enqueue scripts and styles here.
         */
        public function registerScripts()
        {
            wp_enqueue_style('rd-downloads-font-awesome5');

            wp_enqueue_style('rd-downloads-settings-tabs-css');
            wp_enqueue_script('rd-downloads-settings-tabs-js');

            // custom ajax for settings page.
            wp_enqueue_script('rd-downloads-settings-ajax', plugin_dir_url(RDDOWNLOADS_FILE) . 'assets/js/admin/settings/settings-ajax.js', ['jquery'], RDDOWNLOADS_VERSION, true);
            wp_localize_script(
                'rd-downloads-settings-ajax',
                'RdDownloadsSettings',
                [
                    'nonce' => wp_create_nonce('rd-downloads-settings_ajax-settings-nonce'),
                ]
            );

            // you can remove some or all of the line below if you don't use it. ---------
            // css & js for code editor.
            wp_enqueue_style('rd-downloads-settings-ace-editor-css');
            wp_enqueue_script('rd-downloads-ace-editor-js');
            wp_enqueue_script('rd-downloads-settings-ace-editor-js');
        }// registerScripts


    }
}
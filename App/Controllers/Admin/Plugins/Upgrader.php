<?php
/**
 * Upgrade or update the plugin action.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Plugins;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Plugins\\Upgrader')) {
    /**
     * Upgrader class.
     */
    class Upgrader implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        use \RundizDownloads\App\AppTrait;


        /**
         * @var string The upgrader menu slug. This constant must be public.
         */
        const MENU_SLUG = 'rundiz-downloads-manual-update';


        /**
         * Ajax manual update.
         */
        public function ajaxManualUpdate()
        {
            if (!current_user_can('update_plugins')) {
                wp_die(esc_html__('You do not have permission to access this page.', 'rundiz-downloads'), '', ['response' => 403]);
            }

            $output = [];

            if (isset($_SERVER['REQUEST_METHOD']) && strtolower(wp_unslash($_SERVER['REQUEST_METHOD'])) === 'post' && isset($_POST) && !empty($_POST)) {// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                if (check_ajax_referer('rundiz_downloads_nonce', 'security', false) === false) {
                    status_header(403);
                    wp_die(esc_html__('Please reload this page and try again.', 'rundiz-downloads'), '', ['response' => 403]);
                }

                $updateKey = filter_input(INPUT_POST, 'updateKey', FILTER_SANITIZE_NUMBER_INT);

                $Loader = new \RundizDownloads\App\Libraries\Loader();
                $manualUpdateClasses = $Loader->getManualUpdateClasses();
                $maxManualUpdateVersion = 0;
                unset($Loader);

                if (is_array($manualUpdateClasses) && array_key_exists($updateKey, $manualUpdateClasses) && class_exists($manualUpdateClasses[$updateKey])) {
                    $UpdateClass = new $manualUpdateClasses[$updateKey]();

                    try {
                        $UpdateClass->run();// run a manual update single action
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                    }

                    if (!isset($errorMessage) || empty($errorMessage)) {
                        $lastError = error_get_last();
                        if (!empty($lastError)) {
                            if (is_array($lastError) && array_key_exists('message', $lastError) && is_scalar($lastError['message'])) {
                                $errorMessage = $lastError['message'];
                                if (isset($lastError['file']) && isset($lastError['line'])) {
                                    $errorMessage .= ' on ' . $lastError['file'] . ':' . $lastError['line'];
                                }

                                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                                    $debugTraces = debug_backtrace();// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
                                    $errorMessage .= '<br>' . PHP_EOL;
                                    foreach ($debugTraces as $index => $trace) {
                                        $errorMessage .= '[' . $index . ']';
                                        if (isset($trace['file']) && isset($trace['line'])) {
                                            $errorMessage .= esc_html($trace['file'] . ':' . $trace['line']) . '<br>' . PHP_EOL;
                                        }
                                        if (isset($trace['class'])) {
                                            $errorMessage .= '&nbsp; ' . esc_html($trace['class']);
                                            if (isset($trace['type'])) {
                                                $errorMessage .= esc_html($trace['type']);
                                            }
                                        }
                                        if (isset($trace['function'])) {
                                            if (!isset($trace['class'])) {
                                                $errorMessage .= '&nbsp; ';
                                            }
                                            $errorMessage .= esc_html($trace['function']) . '()<br>' . PHP_EOL;
                                        }
                                        if (isset($trace['args'])) {
                                            $errorMessage .= '&nbsp; ' . esc_html(var_export($trace['args'], true)) . '<br>' . PHP_EOL;// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
                                        }
                                    }// endforeach;
                                    unset($index, $trace);
                                    unset($debugTraces);
                                }
                            } else {
                                $errorMessage = esc_html__('An error has been occur, cannot continue manual update. Please contact plugin author.', 'rundiz-downloads');
                            }
                        }
                        unset($lastError);
                    }

                    if (!isset($errorMessage) || (isset($errorMessage) && empty($errorMessage))) {
                        // if there is no error.
                        if (version_compare($maxManualUpdateVersion, $UpdateClass->manual_update_version, '<')) {
                            $maxManualUpdateVersion = $UpdateClass->manual_update_version;
                        }

                        $output['alreadyRunKey'] = $updateKey;
                        $output['alreadyRunClass'] = $manualUpdateClasses[$updateKey];
                        $output['formResultClass'] = 'notice-success';
                        if (array_key_exists(($updateKey + 1), $manualUpdateClasses)) {
                            $output['nextRunKey'] = ($updateKey + 1);
                            $output['formResultMsg'] = __('Success, please click next to continue update.', 'rundiz-downloads');
                        } else {
                            $output['nextRunKey'] = 'end';
                            $output['formResultMsg'] = __('All manual update completed successfully. This page will be no longer available until there is next manual update.', 'rundiz-downloads');

                            $currentConfig = $this->getOptions();
                            $currentConfig['rdsfw_manual_update_version'] = $maxManualUpdateVersion;
                            $this->saveOptions($currentConfig);
                            unset($currentConfig);

                            delete_transient('rundiz_downloads_transient__updated');
                        }
                    } else {
                        // if contain error.
                        status_header(500);
                        $output['formResultClass'] = 'notice-error';
                        $output['formResultMsg'] = $errorMessage;
                    }
                    unset($errorMessage, $UpdateClass);
                } else {
                    status_header(501);
                    $output['formResultClass'] = 'notice-error';
                    $output['formResultMsg'] = __('Unable to run update, there is no update classes to run.', 'rundiz-downloads');
                }

                unset($manualUpdateClasses, $maxManualUpdateVersion, $updateKey);
            }// endif;

            wp_send_json($output);
        }// ajaxManualUpdate


        /**
         * Detect this plugin updated and display link or maybe redirect to manual update page.
         * 
         * This method will be run as new version of code.<br>
         * To understand more about new version of code, please read more on `updateProcessComplete()` method.
         * 
         * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices Reference.
         */
        public function detectPluginUpdate()
        {
            if (get_transient('rundiz_downloads_transient__updated') && current_user_can('update_plugins')) {
                // if there is updated transient
                $Loader = new \RundizDownloads\App\Libraries\Loader();

                if ($Loader->haveManualUpdate() === true) {
                    // if found that there are manual update in this new version of code.
                    // display link or redirect to manual update page. (display link is preferred to prevent bad user experience.)
                    // -------------------------------------------------------------------------------------
                    // display link to manual update page.
                    if (!isset($_REQUEST['page']) || (isset($_REQUEST['page']) && self::MENU_SLUG !== sanitize_text_field(wp_unslash($_REQUEST['page'])))) {// phpcs:ignore WordPress.Security.NonceVerification.Recommended
                        $manualUpdateNotice = '<div class="notice notice-warning is-dismissible">
                            <p>' . 
                                sprintf(
                                    /* translators: %1$s: Open link tag, %2$s: Close link tag. */
                                    __('The Rundiz Downloads is just upgraded and need to be manually update. Please continue to the %1$splugin update page%2$s.', 'rundiz-downloads'), 
                                    '<a href="' . esc_attr(network_admin_url('index.php?page=' . self::MENU_SLUG)) . '">', // this link will be auto convert to admin_url if not in multisite installed.
                                    '</a>'
                                ) . 
                            '</p>
                        </div>';

                        add_action('admin_notices', function () use ($manualUpdateNotice) {
                            echo $manualUpdateNotice . "\n";// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        });
                        add_action('network_admin_notices', function () use ($manualUpdateNotice) {
                            echo $manualUpdateNotice . "\n";// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        });

                        unset($manualUpdateNotice);
                    }// endif;

                    if (is_multisite()) {
                        add_action('network_admin_menu', [$this, 'displayManualUpdateMenu']);
                    } else {
                        add_action('admin_menu', [$this, 'displayManualUpdateMenu']);
                    }

                    add_action('wp_ajax_rundiz_downloads_manualUpdate', [$this, 'ajaxManualUpdate']);
                    // end display link to manual update page.
                    // -------------------------------------------------------------------------------------
                } else {
                    // if don't have any manual update.
                    delete_transient('rundiz_downloads_transient__updated');
                }// endif;

                unset($Loader);
            }// endif;
        }// detectPluginUpdate


        /**
         * Setup manual update page and must be added to admin menu. In this case, add as sub menu of dashboard menu.
         */
        public function displayManualUpdateMenu()
        {
            $hook_suffix = add_dashboard_page(__('Rundiz Downloads update', 'rundiz-downloads'), __('Rundiz Downloads update', 'rundiz-downloads'), 'update_plugins', self::MENU_SLUG, [$this, 'displayManualUpdatePage']);
            add_action('admin_print_styles-' . $hook_suffix, [$this, 'registerStyles']);
            add_action('admin_print_scripts-' . $hook_suffix, [$this, 'registerScripts']);
            unset($hook_suffix);
        }// displayManualUpdateMenu


        /**
         * Display manual update page.
         */
        public function displayManualUpdatePage()
        {
            if (!current_user_can('update_plugins')) {
                wp_die(esc_html__('You do not have permission to access this page.', 'rundiz-downloads'));
            }

            $output = [];

            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $output['manualUpdateClasses'] = $Loader->getManualUpdateClasses();

            $Loader->loadView('admin/Plugins/Upgrader_v', $output);
            unset($Loader, $output);
        }// displayManualUpdatePage


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // on update/upgrade plugin completed. set transient and let `detectPluginUpdate()` work.
            add_action('upgrader_process_complete', [$this, 'updateProcessComplete'], 10, 2);
            // on plugins loaded, display link or maybe redirect to manual update page.
            add_action('plugins_loaded', [$this, 'detectPluginUpdate']);
        }// registerHooks


        /**
         * Enqueue CSS & JS.
         * 
         * This method was called from displayManualUpdateMenu which is active only when plugin is just updated.
         */
        public function registerScripts()
        {
            wp_localize_script(
                'rundiz-downloads-settings-manual-update-js',
                'RdSettingsManualUpdate',
                [
                    'alreadyRunUpdateKey' => '',
                    'alreadyRunUpdateTotal' => 0,
                    'completed' => 'false',
                    'completedTxt' => __('Completed', 'rundiz-downloads'),
                    'dismissNoticeTxt' => __('Dismiss', 'rundiz-downloads'),
                    'nextTxt' => __('Next', 'rundiz-downloads'),
                    'nonce' => wp_create_nonce('rundiz_downloads_nonce'),
                ]
            );

            wp_enqueue_script('rundiz-downloads-settings-manual-update-js');
        }// registerScripts


        /**
         * Enqueue only CSS.
         */
        public function registerStyles()
        {
            wp_enqueue_style('rundiz-downloads-font-awesome5');
        }// registerStyles


        /**
         * After update plugin completed.
         * 
         * This method will be called while running the current version of this plugin, not the new one that just updated.
         * For example: You are running 1.0 and just updated to 2.0. The 2.0 version will not working here yet but 1.0 is working.
         * So, any code here will not work as the new version. Please be aware!
         * 
         * This method will add the transient to be able to detect updated and run the manual update in `detectPluginUpdate()` method.
         * 
         * @link https://developer.wordpress.org/reference/hooks/upgrader_process_complete/ Reference.
         * @link https://codex.wordpress.org/Plugin_API/Action_Reference/upgrader_process_complete Reference.
         * @param \WP_Upgrader $upgrader Upgrader object.
         * @param array $hook_extra Hook extra.
         */
        public function updateProcessComplete(\WP_Upgrader $upgrader, array $hook_extra)
        {
            if (is_array($hook_extra) && array_key_exists('action', $hook_extra) && array_key_exists('type', $hook_extra) && array_key_exists('plugins', $hook_extra)) {
                if ('update' === $hook_extra['action'] && 'plugin' === $hook_extra['type'] && is_array($hook_extra['plugins']) && !empty($hook_extra['plugins'])) {
                    $this_plugin = plugin_basename(RUNDIZDOWNLOADS_FILE);
                    foreach ($hook_extra['plugins'] as $key => $plugin) {
                        if ($this_plugin === $plugin) {
                            // if this plugin is in the updated plugins.
                            // set transient to let it run later. this transient will be called and run in `detectPluginUpdate()` method.
                            set_transient('rundiz_downloads_transient__updated', 1);
                            break;
                        }
                    }// endforeach;
                    unset($key, $plugin, $this_plugin);
                }// endif update plugin and plugins not empty.
            }// endif; $hook_extra
        }// updateProcessComplete


    }// Upgrader
}

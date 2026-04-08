<?php
/**
 * Upgrade or update the plugin action.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Plugins;


if (!defined('ABSPATH')) {
    exit();
}


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Plugins\\Upgrader')) {
    /**
     * Plugin upgrader class.
     */
    class Upgrader implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        use \RundizDownloads\App\AppTrait;


        /**
         * @var string The upgrader menu slug. This constant must be public.
         */
        const MENU_SLUG = 'rundiz-downloads-manual-update';


        /**
         * @var string The current admin page.
         */
        private $hookSuffix = '';


        /**
         * Ajax manual update.
         */
        public function ajaxManualUpdate()
        {
            if (!current_user_can('update_plugins')) {
                wp_die(
                    esc_html__('You do not have permission to access this page.', 'rundiz-downloads'), 
                    '', 
                    ['response' => 403]
                );
            }

            $output = [];

            if (isset($_SERVER['REQUEST_METHOD']) && strtolower(wp_unslash($_SERVER['REQUEST_METHOD'])) === 'post' && isset($_POST) && !empty($_POST)) {// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                // if method POST and there is POST data.
                if (check_ajax_referer('rundiz_downloads_nonce', 'security', false) === false) {
                    status_header(403);
                    wp_die(
                        esc_html__('Please reload this page and try again.', 'rundiz-downloads'), 
                        '', 
                        ['response' => 403]
                    );
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
                                $errorMessage = __('An error has been occur, cannot continue manual update. Please contact plugin author.', 'rundiz-downloads');
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
         * Allow code/WordPress to call hook `admin_enqueue_scripts` 
         * then `wp_register_script()`, `wp_localize_script()`, `wp_enqueue_script()` functions will be working fine later.
         * 
         * @link https://wordpress.stackexchange.com/a/76420/41315 Original source code.
         * @since 1.1.2
         */
        public function callEnqueueHook()
        {
            add_action('admin_enqueue_scripts', [$this, 'registerStyles']);
            add_action('admin_enqueue_scripts', [$this, 'registerScripts']);
        }// callEnqueueHook


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
                                    // translators: %1$s Open link, %2$s Close link.
                                    esc_html__('The Rundiz Downloads is just upgraded and need to be manually update. Please continue to the %1$splugin update page%2$s.', 'rundiz-downloads'),
                                    '<a href="' . esc_url(network_admin_url('index.php?page=' . self::MENU_SLUG)) . '">', // this link will be auto convert to admin_url if not in multisite installed.
                                    '</a>'
                                ) .
                            '</p>
                        </div>';

                        add_action('admin_notices', function () use ($manualUpdateNotice) {
                            // the line below will be echo out custom HTML. So, it cannot be and must not escape or the result will be broken.
                            echo $manualUpdateNotice . "\n";// phpcs:ignore WordPress.Security.EscapeOutput
                        });
                        add_action('network_admin_notices', function () use ($manualUpdateNotice) {
                            // the line below will be echo out custom HTML. So, it cannot be and must not escape or the result will be broken.
                            echo $manualUpdateNotice . "\n";// phpcs:ignore WordPress.Security.EscapeOutput
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
            if (is_string($hook_suffix)) {
                $this->hookSuffix = $hook_suffix;
                add_action('load-' . $hook_suffix, [$this, 'callEnqueueHook']);
            }
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
            // On update/upgrade plugin completed, set transient and let `detectPluginUpdate()` work.
            add_action('upgrader_process_complete', [$this, 'updateProcessComplete'], 10, 2);
            // On WordPress has finished loading but before any headers are sent, display link or maybe redirect to manual update page.
            add_action('init', [$this, 'detectPluginUpdate']);
        }// registerHooks


        /**
         * Enqueue CSS & JS.
         *
         * This method was called from displayManualUpdateMenu which is active only when plugin is just updated.
         * 
         * @param string $hook_suffix The current admin page.
         */
        public function registerScripts($hook_suffix = '')
        {
            if ($hook_suffix !== $this->hookSuffix) {
                return;
            }

            wp_localize_script(
                'rundiz-downloads-settings-manual-update-js',
                'RundizDownloadsRdSettingsManualUpdate',
                [
                    'alreadyRunUpdateKey' => '',
                    'alreadyRunUpdateTotal' => 0,
                    'completed' => 'false',
                    'nonce' => wp_create_nonce('rundiz_downloads_nonce'),
                    'txtCompleted' => __('Completed', 'rundiz-downloads'),
                    'txtDismissNotice' => __('Dismiss', 'rundiz-downloads'),
                    'txtNext' => __('Next', 'rundiz-downloads'),
                ]
            );

            wp_enqueue_style('rundiz-downloads-font-awesome5');

            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $manualUpdateClasses = $Loader->getManualUpdateClasses();
            unset($Loader);
            wp_add_inline_script('rundiz-downloads-settings-manual-update-js', 'var manualUpdateClasses = ' . (!empty($manualUpdateClasses) ? wp_json_encode($manualUpdateClasses) : '') . ';');
            unset($manualUpdateClasses);

            wp_enqueue_script('rundiz-downloads-settings-manual-update-js');
        }// registerScripts


        /**
         * Enqueue only CSS.
         * 
         * @param string $hook_suffix The current admin page.
         */
        public function registerStyles($hook_suffix = '')
        {
            if ($hook_suffix !== $this->hookSuffix) {
                return;
            }

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
         * @link https://developer.wordpress.org/reference/classes/wp_upgrader/ Reference.
         * @param \WP_Upgrader $upgrader The `\WP_Upgrader` class.
         * @param array $hook_extra Array of bulk item update data.
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

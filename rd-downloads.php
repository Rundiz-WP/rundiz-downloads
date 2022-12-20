<?php
/**
 * Plugin Name: Rundiz Downloads
 * Plugin URI: https://rundiz.com/?p=319
 * Description: Download manager for WordPress that support GitHub auto update.
 * Version: 1.0.11
 * Requires at least: 4.6.0
 * Requires PHP: 5.5
 * Author: Vee Winch
 * Author URI: https://rundiz.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: rd-downloads
 * Domain Path: /App/languages/
 *
 * @package rd-downloads
 */


// define this plugin main file path.
if (!defined('RDDOWNLOADS_FILE')) {
    define('RDDOWNLOADS_FILE', __FILE__);
}


if (!defined('RDDOWNLOADS_VERSION')) {
    $pluginData = (function_exists('get_file_data') ? get_file_data(__FILE__, ['Version' => 'Version']) : null);
    $pluginVersion = (isset($pluginData['Version']) ? $pluginData['Version'] : date('Ym'));
    unset($pluginData);

    define('RDDOWNLOADS_VERSION', $pluginVersion);

    unset($pluginVersion);
}


// include this plugin's autoload.
require __DIR__.'/autoload.php';


// initialize plugin app main class.
$this_plugin_app = new \RdDownloads\App\App();
$this_plugin_app->run();
unset($this_plugin_app);
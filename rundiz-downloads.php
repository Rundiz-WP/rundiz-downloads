<?php
/**
 * Plugin Name: Rundiz Downloads
 * Plugin URI: https://rundiz.com/?p=319
 * Description: Download manager for WordPress that support GitHub auto update.
 * Version: 1.0.18dev-20260325
 * Requires at least: 4.7.0
 * Requires PHP: 5.5
 * Author: Vee Winch
 * Author URI: https://rundiz.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: rundiz-downloads
 * Domain Path: /App/languages/
 *
 * @package rundiz-downloads
 */


if (!defined('ABSPATH')) {
    exit;
}


// define this plugin main file path.
if (!defined('RUNDIZDOWNLOADS_FILE')) {
    define('RUNDIZDOWNLOADS_FILE', __FILE__);
}


if (!defined('RUNDIZDOWNLOADS_VERSION')) {
    $rundiz_downloads_pluginData = (function_exists('get_file_data') ? get_file_data(__FILE__, ['Version' => 'Version']) : null);
    // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
    $rundiz_downloads_pluginVersion = (isset($rundiz_downloads_pluginData['Version']) ? $rundiz_downloads_pluginData['Version'] : date('Ym'));
    unset($rundiz_downloads_pluginData);

    define('RUNDIZDOWNLOADS_VERSION', $rundiz_downloads_pluginVersion);

    unset($rundiz_downloads_pluginVersion);
}


// include this plugin's autoload.
require __DIR__ . '/autoload.php';


// initialize plugin app main class.
$rundiz_downloads_App = new \RundizDownloads\App\App();
$rundiz_downloads_App->run();
unset($rundiz_downloads_App);

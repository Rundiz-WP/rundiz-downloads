=== Rundiz Downloads ===
Contributors: okvee
Tags: downloads, download, download manager, file hosting, GitHub
Requires at least: 4.0
Tested up to: 5.0.2
Stable tag: 0.3
Requires PHP: 5.5
License: MIT
License URI: https://opensource.org/licenses/MIT

Download manager for WordPress that support GitHub auto update.

== Description ==
Rundiz Downloads is a files, documents management that support GitHub auto update. Provide the download link, track download files.

The GitHub auto update means the URL and file size will be update automatically on GitHub commit or new tag/release.
You can change your setting to accept how often of auto update. Every releases and commits, every releases, or none.

= Features =

 * Manage files locally or hosted on GitHub or any remote files.
 * Auto update download URL once GitHub repository was updated.
 * Setting GitHub auto update to none, every releases, every releases and commits.
 * Manual update multiple remote file data such as file size that was changed.
 * Use version range to compare tags before update.
 * Insert download button on classic editor or TinyMCE.
 * Download logs/statistic.
 * Admin dashboard statistic widget.
 * Block bots from downloading by captcha, user agent.
 * Captcha with the audio.
 * Redirect to file or force download. (The force download will be working with local file only.)
 * Support shortcode.
 * Customisable download element for shortcode.
 * Automatically delete logs older than specific number of days.
 * Localisation support

= System requirement =
PHP 5.5 or higher
WordPress 4.0 or higher

== Installation ==
1. Upload "rd-downloads" folder to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Access plugin setup page.
4. Follow setup instruction on screen.

== Frequently Asked Questions ==
= Does it support multisite? =
Yes, it is.

= Will the uploaded files be deleted on uninstall? =
Yes, the uploaded files and folders in WordPress upload folder will be deleted on plugin uninstall.

= Will the plugin's tables be dropped on uninstall? =
Yes, the plugin's tables will be dropped on uninstall.

== Screenshots ==
1. Front-end download button and description example.
2. Manage downloads page.
3. Add or edit download page.
4. Download logs page.
5. Plugin settings tab 1 (General).
6. Plugin settings tab 2 (GitHub settings).
7. Plugin settings tab 3 (Log/statistic).
8. Plugin settings tab 4 (Design). This will be convert from shortcode into the element you design.
9. Add a download button for TinyMCE (classic editor).
10. Add a download dialog after clicked on the button in classic editor.

== Changelog ==
= 0.3 =
2018-12-27

* Fix "Page not found" title where the website has Yoast SEO plugin.
* Add conditional to template to display not found text.
* Remove un-necessary files from "securimage" vendor.
* Update text translations.
* Use filters to allow other plugins, themes to use their own captcha. (Removed action hook.)

= 0.2 =
2018-12-26

* Fix get URL for download to be always at home url.
* Management page, make download name show more characters.
* Management page, add link to GitHub repository main page.
* Automatically clear all cache on saved settings or download data.
* Add download file version field.
* Add download file version range field for GitHub tags comparison.
* Use version range compare in editing page.
* Use version range compare in ajax save (add and edit download).
* Use version range compare in bulk actions.
* Use version range compare in GitHub auto update.

= 0.1 =
2018-12-24

* The beginning.
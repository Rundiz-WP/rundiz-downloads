=== Rundiz Downloads ===
Contributors: okvee
Tags: downloads, download, download manager, file hosting, GitHub
Tested up to: 6.0
Stable tag: 1.0.10
License: MIT
License URI: https://opensource.org/licenses/MIT
Requires at least: 4.6.0
Requires PHP: 5.5

Download manager for WordPress that support GitHub auto update.

== Description ==
Rundiz Downloads is a files, documents management that support GitHub auto update. Provide the download link, track download files.

The GitHub auto update means the URL and file size will be update automatically on GitHub commit or new tag/release.
You can change your setting to accept how often of auto update. Every releases and commits, every releases, or none.

= Features =

 * Manage files locally or hosted on GitHub or any remote files.
 * Use GitHub OAuth to manage webhooks (webhook is for auto update). It will be super easy if you have many users or repositories.
 * Auto update download URL and version once GitHub repository was updated.
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
WordPress 4.6.0 or higher

Browse the source code, bug report, pull request on [GitHub repository](https://github.com/Rundiz-WP/rundiz-downloads). 

== Installation ==
1. Upload "rd-downloads" folder to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Access plugin setup page.
4. Follow setup instruction on screen.

== Frequently Asked Questions ==
= Does it support multisite? =
Yes, it is. However the network activation for multisite will not working, you can only activate the plugin per site that have to use it.

= Will the uploaded files be deleted on uninstall? =
Yes, the uploaded files and folders in WordPress upload folder will be deleted on plugin uninstall.

= Will the plugin's tables be dropped on uninstall? =
Yes, the plugin's tables will be dropped on uninstall.

== Screenshots ==
1. Front-end download button and description example.
2. Manage downloads page.
3. Add or edit download page.
4. Download logs page.
5. GitHub OAuth page.
6. Plugin settings tab 1 (General).
7. Plugin settings tab 2 (Anti robots settings).
8. Plugin settings tab 3 (GitHub settings).
9. Plugin settings tab 4 (Statistic/Logs).
10. Plugin settings tab 5 (Design). This will be convert from shortcode into the element you design.
11. Add a download button for TinyMCE (classic editor).
12. Add a download dialog after clicked on the button in classic editor.

== Changelog ==
= 1.0.10 =
2022-03-03

* Fix `count()` error on PHP 7.2+.

= 1.0.9 =
2022-01-23

* Remove use dependency CSS to let dequeue duplicated Font Awesome work.
* Re-order the process in downloads.

= 1.0.8 =
2021-12-21

* Fix disconnect page.
* Update GitHub OAuth settings page.
* Remove abandoned Securimage class.

Previous version updates:  
Please read on changelog.md
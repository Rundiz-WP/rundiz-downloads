# Change log

## Version 1.x

### 1.0.8
2021-12-21

* Fix disconnect page.
* Update GitHub OAuth settings page.
* Remove abandoned Securimage class.

### 1.0.7
2021-12-14

* Fix error `FILTER_SANITIZE_STRING` is deprecated in PHP 8.1.
* Update translation.
* Update WPListTable class based on WordPress 5.8.2.

### 1.0.6
2021-08-18

* Update Securimage to work with PHP 8.0.

### 1.0.5
2020-12-27

* Add `session_write_close()` to class destructor to prevent site health error about **An active PHP session was detected**, **The REST API encountered an error**, and **Your site could not complete a loopback request**.

### 1.0.4
2020-11-06

* Fix `Github->validateGitHubWebhook()` method to accept different case of `x-hub-signature` that was sent from GitHub.

### 1.0.3
2019-08-19

* Fix bug that cannot manually clear logs.

### 1.0.2
2019-03-07

* Update WPListTable class to work with WP 5.1+.

### 1.0.1
2019-01-23

* Update tested up to (WordPress).
* Fix load language in wrong path.
* Update required at least (WordPress).
* Update translation.

### 1.0
2019-01-09

* Use new OAuth to sync webhook for repos. (Use OAuth to sync webhook and secret key for multiple repositories.)
* Add function to remove webhook on delete download item.
* Remove old way to manually add webhook per repository.
* Add filters for cache lifetime.
* Add more cleanup scheduled hooks, user options on uninstall the plugin.
* Add user entered wrong captcha log.
* Display total wrong captcha in dashboard widget graph.
* Fix capability check for user who is "Author" can't use search dialog in classic editor.
* Fix querystring data not encoded.
* Change from CURL to wp_remote_xxx function.
* Update translation.

## Version 0.x

### 0.4
2018-12-29

* Fix `if` template condition.
* Change required token's scopes in config.
* Re-order tag names that have got from GitHub.
* Improve saving process.
* Fix check URL before get remote data.
* Fix version range and change some files structure.

### 0.3
2018-12-27

* Fix "Page not found" title where the website has Yoast SEO plugin.
* Add conditional to template to display not found text.
* Remove un-necessary files from "securimage" vendor.
* Update text translations.
* Use filters to allow other plugins, themes to use their own captcha. (Removed action hook.)

### 0.2
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

### 0.1
2018-12-24

* The beginning.
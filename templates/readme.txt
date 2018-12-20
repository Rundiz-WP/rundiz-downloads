This folder is for place template file that will be loaded by plugin and displayed use locate_template() and load_template() functions.

If the `$view_name` is `mydir/mypage`.
It will look up in `wp-content/themes/%your theme%/rd-downloads/templates/mydir/mypage.php`> first.
If not found then it will look up in `wp-content/plugins/rd-downloads/templates/mydir/mypage.php`.
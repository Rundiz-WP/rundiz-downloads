<div class="rundiz-downloads_dashboard-widget">
    <canvas id="rundiz-downloads_dashboard-widget_all-downloads-daily-stat"></canvas>

    <h3><?php esc_html_e('Top Downloads', 'rundiz-downloads'); ?></h3>
    <div id="rundiz-downloads_dashboard-widget_top-results-text" class="rundiz-downloads_dashboard-widget_top-results-text rundiz-downloads_dashboard-widget_-top-results"></div>
    <ol id="rundiz-downloads_dashboard-widget_top-results-list" class="rundiz-downloads_dashboard-widget_top-results-list rundiz-downloads_dashboard-widget_-top-results hidden"></ol>

    <div class="rundiz-downloads_dashboard-widget_top-results-filter">
        <select id="rundiz-downloads_dashboard-widget_top-results-filter-select" disabled="disabled">
            <option value="1"><?php esc_html_e('Last 24 hours', 'rundiz-downloads'); ?></option>
            <option value="7"><?php esc_html_e('Last 7 days', 'rundiz-downloads'); ?></option>
            <option value="30"><?php esc_html_e('Last 30 days', 'rundiz-downloads'); ?></option>
            <option value="0" selected="selected"><?php esc_html_e('All time', 'rundiz-downloads'); ?></option>
        </select>
        <span class="rundiz-downloads_dashboard-widget_credit"><?php esc_html_e('Rundiz Downloads', 'rundiz-downloads'); ?></span>
    </div>
</div>


<!-- do not use HTML template tag because `wp.template()` will not work. -->
<script type="text/html" id="tmpl-rundiz-downloads-list-top-item">
    <li>
        <a href="<?php echo esc_url(admin_url('admin.php?page=' . RundizDownloads\App\Controllers\Admin\Downloads\Menu::SUB_MENU_SLUG_EDIT . '&download_id=')); ?>{{{data.download_id}}}">{{{data.download_name}}}</a>
        <span class="rundiz-downloads_total-downloads">{{{data.download_count}}}</span>
    </li>
</script>
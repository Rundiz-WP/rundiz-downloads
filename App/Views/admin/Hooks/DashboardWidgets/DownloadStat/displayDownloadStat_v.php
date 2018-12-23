<div class="rd-downloads_dashboard-widget">
    <canvas id="rd-downloads_dashboard-widget_all-downloads-daily-stat"></canvas>

    <h3><?php _e('Top Downloads', 'rd-downloads'); ?></h3>
    <div id="rd-downloads_dashboard-widget_top-results-text" class="rd-downloads_dashboard-widget_top-results-text rd-downloads_dashboard-widget_-top-results"></div>
    <ol id="rd-downloads_dashboard-widget_top-results-list" class="rd-downloads_dashboard-widget_top-results-list rd-downloads_dashboard-widget_-top-results hidden"></ol>

    <div class="rd-downloads_dashboard-widget_top-results-filter">
        <select id="rd-downloads_dashboard-widget_top-results-filter-select" disabled="disabled">
            <option value="1"><?php _e('Last 24 hours', 'rd-downloads'); ?></option>
            <option value="7"><?php _e('Last 7 days', 'rd-downloads'); ?></option>
            <option value="30"><?php _e('Last 30 days', 'rd-downloads'); ?></option>
            <option value="0" selected="selected"><?php _e('All time', 'rd-downloads'); ?></option>
        </select>
        <span class="rd-downloads_dashboard-widget_credit"><?php _e('Rundiz Downloads', 'rd-downloads'); ?></span>
    </div>
</div>


<script type="text/html" id="tmpl-rd-downloads-list-top-item">
    <li>
        <a href="<?php echo esc_url(admin_url('admin.php?page=rd-downloads_edit&download_id=')); ?>{{{data.download_id}}}">{{{data.download_name}}}</a>
        <span class="rd-downloads_total-downloads">{{{data.download_count}}}</span>
    </li>
</script>
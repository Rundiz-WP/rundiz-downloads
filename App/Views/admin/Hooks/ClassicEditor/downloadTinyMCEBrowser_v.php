<table class="form-table">
    <tr>
        <th><?php esc_html_e('Search', 'rundiz-downloads'); ?></th>
        <td>
            <input id="rundiz-downloads-search-input" class="rundiz-downloads-input-full" type="text" name="search" autofocus="">
            <span class="rundiz-downloads-inside-input-icon fas fa-spinner fa-pulse hidden"></span>
        </td>
    </tr>
</table>
<div id="rundiz-downloads-search-result"></div>


<!-- do not use HTML template tag because `wp.template()` will not work. -->
<script type="text/html" id="tmpl-rundiz-downloads-search-table-result">
    <?php esc_html_e('Found total', 'rundiz-downloads'); ?>: {{data.total}}
    <div class="rundiz-downloads-table-responsive">
        <table class="striped rundiz-downloads-search-result-table">
            <thead>
                <tr>
                    <th class="download_name"><?php esc_html_e('Downloads name', 'rundiz-downloads'); ?></th>
                    <th class="download_type"><?php esc_html_e('Type', 'rundiz-downloads'); ?></th>
                    <th class="download_file_name"><?php esc_html_e('File', 'rundiz-downloads'); ?></th>
                    <th class="download_size"><?php esc_html_e('Size', 'rundiz-downloads'); ?></th>
                    <th class="actions"></th><!--action column-->
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div><!--.rundiz-downloads-table-responsive-->
    <div class="rundiz-downloads-ajax-search-paging-buttons">
        <span class="rundiz-downloads-current-paging-status">
            <?php 
            printf(
                /* translators: %1$s: Current page, %2$s: Total pages. */
                esc_html__('Page %1$s of %2$s', 'rundiz-downloads'),
                '<span class="current-page">{{data.current_page}}</span>',
                '<span class="total-pages">{{data.total_pages}}</span>'
            ); 
            ?>
        </span><!--.rundiz-downloads-current-paging-status-->
        <# if (data.current_page > 1) { #>
        <button class="button rundiz-downloads-ajax-search-paging-button" onclick="return rdDownloadsAjaxSearch(jQuery('#rundiz-downloads-search-input').val(), {{data.previous_page}});"><?php esc_html_e('Previous', 'rundiz-downloads'); ?></button>
        <# } #>
        <#if (data.current_page < data.total_pages) { #>
        <button class="button rundiz-downloads-ajax-search-paging-button" onclick="return rdDownloadsAjaxSearch(jQuery('#rundiz-downloads-search-input').val(), {{data.next_page}});"><?php esc_html_e('Next', 'rundiz-downloads'); ?></button>
        <# } #>
    </div><!--.rundiz-downloads-ajax-search-paging-buttons-->
</script>


<!-- do not use HTML template tag because `wp.template()` will not work. -->
<script type="text/html" id="tmpl-rundiz-downloads-search-list-item">
    <tr>
        <td>
            {{data.download_name}}
            <div class="rundiz-downloads-admin-comment">{{{data.download_admin_comment}}}</div>
        </td>
        <td>{{data.type}}</td>
        <td>{{data.download_file_name}}</td>
        <td>{{data.size}}</td>
        <td><button class="button rundiz-downloads-insert-shortcode-button" data-download_id="{{data.download_id}}" onclick="return rdDownloadsInsertShortCodeButton(this);"><?php esc_html_e('Insert', 'rundiz-downloads'); ?></button></td>
    </tr>
</script>
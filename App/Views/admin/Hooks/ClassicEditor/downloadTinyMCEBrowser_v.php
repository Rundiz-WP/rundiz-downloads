<table class="form-table">
    <tr>
        <th><?php _e('Search', 'rd-downloads'); ?></th>
        <td>
            <input id="rd-downloads-search-input" class="rd-downloads-input-full" type="text" name="search" autofocus="">
            <span class="rd-downloads-inside-input-icon fas fa-spinner fa-pulse hidden"></span>
        </td>
    </tr>
</table>
<div id="rd-downloads-search-result"></div>


<script type="text/html" id="tmpl-rd-downloads-search-table-result">
    <?php _e('Found total', 'rd-downloads'); ?>: {{data.total}}
    <div class="rd-downloads-table-responsive">
        <table class="striped rd-downloads-search-result-table">
            <thead>
                <tr>
                    <th class="download_name"><?php _e('Downloads name', 'rd-downloads'); ?></th>
                    <th class="download_type"><?php _e('Type', 'rd-downloads'); ?></th>
                    <th class="download_file_name"><?php _e('File', 'rd-downloads'); ?></th>
                    <th class="download_size"><?php _e('Size', 'rd-downloads'); ?></th>
                    <th class="actions"></th><!--action column-->
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div><!--.rd-downloads-table-responsive-->
    <div class="rd-downloads-ajax-search-paging-buttons">
        <span class="rd-downloads-current-paging-status">
            <?php 
            printf(
                /* translators: %1$s: Current page, %2$s: Total pages. */
                __('Page %1$s of %2$s', 'rd-downloads'),
                '<span class="current-page">{{data.current_page}}</span>',
                '<span class="total-pages">{{data.total_pages}}</span>'
            ); 
            ?>
        </span><!--.rd-downloads-current-paging-status-->
        <# if (data.current_page > 1) { #>
        <button class="button rd-downloads-ajax-search-paging-button" onclick="return rdDownloadsAjaxSearch(jQuery('#rd-downloads-search-input').val(), {{data.previous_page}});"><?php _e('Previous', 'rd-downloads'); ?></button>
        <# } #>
        <#if (data.current_page < data.total_pages) { #>
        <button class="button rd-downloads-ajax-search-paging-button" onclick="return rdDownloadsAjaxSearch(jQuery('#rd-downloads-search-input').val(), {{data.next_page}});"><?php _e('Next', 'rd-downloads'); ?></button>
        <# } #>
    </div><!--.rd-downloads-ajax-search-paging-buttons-->
</script>


<script type="text/html" id="tmpl-rd-downloads-search-list-item">
    <tr>
        <td>
            {{data.download_name}}
            <div class="rd-downloads-admin-comment">{{{data.download_admin_comment}}}</div>
        </td>
        <td>{{data.type}}</td>
        <td>{{data.download_file_name}}</td>
        <td>{{data.size}}</td>
        <td><button class="button rd-downloads-insert-shortcode-button" data-download_id="{{data.download_id}}" onclick="return rdDownloadsInsertShortCodeButton(this);"><?php _e('Insert', 'rd-downloads'); ?></button></td>
    </tr>
</script>
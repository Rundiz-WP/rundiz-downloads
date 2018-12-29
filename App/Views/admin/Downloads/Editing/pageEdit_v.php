<div class="wrap">
    <h1><?php if (isset($page_heading1)) {echo $page_heading1;} ?></h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php echo $form_result_class; ?> notice is-dismissible">
        <p>
            <strong><?php echo $form_result_msg; ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.'); ?></span></button>
    </div>
    <?php } ?> 
    <div class="rd-downloads-form-result-placeholder"></div>

    <form id="rd-downloads-edit-form" class="rd-downloads-edit-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field(); ?> 
        <input id="download_id" type="hidden" name="download_id" value="<?php if (isset($download_id)) {echo esc_attr($download_id);} ?>">

        <div id="titlediv" class="rd-downloads-title-div">
            <input id="title" type="text" name="download_name" value="<?php if (isset($download_name)) {echo esc_attr($download_name);} ?>" maxlength="200" placeholder="<?php esc_attr_e('Downloads name', 'rd-downloads'); ?>">
        </div>
        <table class="form-table">
            <tbody>
                <tr>
                    <th><label for="download_url"><?php _e('Download URL', 'rd-downloads'); ?></label></th>
                    <td>
                        <input id="download_type" type="hidden" name="download_type" value="<?php if (isset($download_type)) {echo esc_attr($download_type);} ?>">
                        <input id="download_url" class="rd-downloads-input-full" type="url" name="download_url" value="<?php if (isset($download_url)) {echo esc_attr($download_url);} ?>" maxlength="300" required="">
                        <input id="download_related_path" type="hidden" name="download_related_path" value="<?php if (isset($download_related_path)) {echo esc_attr($download_related_path);} ?>">
                        <input id="download_size" type="hidden" name="download_size" value="<?php if (isset($download_size)) {echo esc_attr($download_size);} ?>">
                        <div id="download-size-and-preview" class="download-size-and-preview"><?php 
                        if (isset($download_size)) {
                            echo size_format($download_size);
                        }
                        if (isset($download_url)) {
                            echo ' ';
                            echo '<a href="' . esc_attr($download_url) . '" target="preview-file" title="' . esc_attr__('Preview', 'rd-downloads') . '"><i class="fas fa-eye fa-fw rd-downloads-icon-preview"></i> <span class="sr-only">' . __('Preview', 'rd-downloads') . '</span></a>';
                        }
                        ?></div>
                        <p class="description"><?php _e('Please open the help tab to see help message.', 'rd-downloads'); ?></p>
                        <hr>
                        <div class="rd-downloads-form-type-local-file-browser rd-downloads-dropzone"></div>
                        <div class="rd-downloads-local-buttons">
                            <div class="rd-downloads-file-upload-button button">
                                <span><i class="fas fa-upload icon-upload"></i> <?php _e('Upload new file', 'rd-downloads'); ?></span>
                                <input id="rd-downloads-local-input-file" type="file">
                            </div>
                            <button class="rd-downloads-reload-button button" type="button" onclick="rdDownloadsAjaxFileBrowser();"><i class="fas fa-sync-alt icon-reload"></i> <?php _e('Reload', 'rd-downloads'); ?></button>
                        </div><!--.rd-downloads-local-buttons-->
                        <p class="description"><?php 
                        /* translators: %s: Max upload size. */
                        printf(__('Maximum upload file size: %s.', 'rd-downloads'), size_format(wp_max_upload_size())); 
                        ?></p>
                    </td>
                </tr>
                <tr class="rd-downloads-opt_download_version">
                    <th><label for="opt_download_version"><?php _e('Version', 'rd-downloads'); ?></label></th>
                    <td>
                        <input id="opt_download_version" class="regular-text" type="text" name="opt_download_version" value="<?php if (isset($opt_download_version)) {echo esc_attr($opt_download_version);} ?>" maxlength="50">
                        <p class="description">
                            <?php 
                            printf(
                                /* translators: %s: example version number. 1.0.0 */
                                __('The version number of this download. Example: %s', 'rd-downloads'), 
                                '1.2.3'
                            ); 
                            ?>
                        </p>
                    </td>
                </tr>
                <tr class="rd-downloads-opt_download_version_range<?php if (isset($download_type) && $download_type != '1') {echo ' hidden';} ?>">
                    <th><label for="opt_download_version_range"><?php _e('Version range', 'rd-downloads'); ?></label></th>
                    <td>
                        <input id="opt_download_version_range" class="regular-text" type="text" name="opt_download_version_range" value="<?php if (isset($opt_download_version_range)) {echo esc_attr($opt_download_version_range);} ?>" maxlength="50">
                        <p class="description">
                            <?php 
                            _e('The version range to get update.', 'rd-downloads');
                            echo ' ';
                            printf(
                                /* translators: %1$s: Open link, %2$s: Close link. */
                                __('We use the same versions and constraints as %1$scomposer%2$s.', 'rd-downloads'),
                                '<a href="https://getcomposer.org/doc/articles/versions.md" target="composer_versionsconstraints">',
                                '</a>'
                            ); 
                            echo ' ';
                            /* translators: %s: Example version constraints. >=your current version */
                            printf(__('Default is %s', 'rd-downloads'), '<code>&gt;=your current version</code>');
                            ?><br>
                            <?php _e('This is for GitHub download only.', 'rd-downloads'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="download_admin_comment"><?php _e('Notes', 'rd-downloads'); ?></label></th>
                    <td>
                        <textarea id="download_admin_comment" class="widefat" name="download_admin_comment" maxlength="1000" rows="3"><?php if (isset($download_admin_comment)) {echo esc_html($download_admin_comment);} ?></textarea>
                        <p class="description"><?php _e('Notes about this download, display to administrator only. HTML will be escaped.', 'rd-downloads'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="download_count"><?php _e('Download count', 'rd-downloads'); ?></label></th>
                    <td>
                        <input id="download_count" class="regular-text" type="number" name="download_count" value="<?php if (isset($download_count)) {echo esc_attr($download_count);} ?>" min="0" max="99999999999" step="1" placeholder="0">
                        <p class="description"><?php _e('The number of times this file has been downloaded.', 'rd-downloads'); ?></p>
                    </td>
                </tr>
                <tr class="rd-downloads-opt_force_download<?php if (isset($download_type) && $download_type != '0') {echo ' hidden';} ?>">
                    <th><label for="opt_force_download"><?php _e('Force download', 'rd-downloads'); ?></label></th>
                    <td>
                        <label><input type="radio" name="opt_force_download" value="1"<?php if (isset($opt_force_download) && $opt_force_download == '1') {echo ' checked="checked"';} ?>><?php _e('Yes', 'rd-downloads'); ?></label> &nbsp;
                        <label><input type="radio" name="opt_force_download" value="0"<?php if (isset($opt_force_download) && $opt_force_download == '0') {echo ' checked="checked"';} ?>><?php _e('No', 'rd-downloads'); ?></label> &nbsp;
                        <label>
                            <input type="radio" name="opt_force_download" value=""<?php if (!isset($opt_force_download) || (isset($opt_force_download) && $opt_force_download == '')) {echo ' checked="checked"';} ?>>
                            <?php _e('Default', 'rd-downloads'); ?> 
                            <span class="description">(<?php
                            if (isset($rd_downloads_options['rdd_force_download']) && $rd_downloads_options['rdd_force_download'] == '1') {
                                $pluginSettingsUse = __('Force download', 'rd-downloads');
                            } else {
                                $pluginSettingsUse = __('redirect to file', 'rd-downloads');
                            }
                            /* translators: %s: Force download option value for main plugin setting. */
                            echo sprintf(__('The plugin setting is using %s.', 'rd-downloads'), $pluginSettingsUse);
                            unset($pluginSettingsUse);
                            ?>)</span>
                        </label>
                        <p class="description">
                            <?php _e('Choose yes and the file will use force download instead of redirect to it.', 'rd-downloads'); ?> 
                            <?php _e('Please open the help tab to see help message.', 'rd-downloads'); ?> 
                        </p>
                    </td>
                </tr>
                <tr class="rd-downloads-publish-data">
                    <th><label><?php _e('Publish data', 'rd-downloads'); ?></label></th>
                    <td>
                        <p><i class="fas fa-user"></i> <?php _e('Created by', 'rd-downloads'); ?>: <span class="create-by"><?php if (isset($user_id)) {echo '<a href="' . get_edit_user_link($user_id) . '" target="editUser">';} ?><?php if (isset($display_name)) {echo $display_name;} ?><?php if (isset($user_id)) {echo '</a>';} ?></span></p>
                        <p><i class="fas fa-calendar-alt"></i> <?php _e('Created on', 'rd-downloads'); ?>: <span class="create-on"><?php if (isset($download_create_gmt)) {echo RdDownloads\App\Libraries\DateTime::displayDateTime($download_create_gmt);} ?></span></p>
                        <p><i class="fas fa-calendar-alt"></i> <?php _e('Last update', 'rd-downloads'); ?>: <span class="last-update"><?php if (isset($download_update_gmt)) {echo RdDownloads\App\Libraries\DateTime::displayDateTime($download_update_gmt);} ?></span></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button id="submit" class="button button-primary rd-downloads-save-form-button" type="submit" name="submit" value="save"><?php _e('Save', 'rd-downloads'); ?></button>
        </p>
    </form><!--#rd-downloads-edit-form-->

    <template id="tmpl-selected-download-file-size">
        {{{data.size}}} <a href="{{data.url}}" target="preview-file" title="<?php esc_attr_e('Preview', 'rd-downloads'); ?>"><i class="fas fa-eye fa-fw rd-downloads-icon-preview"></i> <span class="sr-only"><?php _e('Preview', 'rd-downloads'); ?></span></a>
    </template>
    
    <script type="text/html" id="tmpl-file-browser-list-item">
        <li id="{{{data.id}}}" class="<# if (data.isFile === true) { #>item-file<# } else { #>item-folder<# } #>">
            <# if (data.isFile === true) { #>
                <!-- this is file -->
                <!-- link to select file -->
                <a onclick="rdDownloadsSelectLocalFile(this);" data-isdeletable="{{{data.isDeletable}}}" data-size="{{{data.readableFileSize}}}" data-url="{{{data.url}}}" data-relatedpath="{{{data.relatedPathEscaped}}}">
                    <i class="fas fa-file icon-file"></i> {{{data.filename}}} ({{{data.readableFileSize}}})
                </a>
                <!-- link to preview -->
                <a href="{{{data.url}}}" target="preview-file" title="<?php esc_attr_e('Preview', 'rd-downloads'); ?>">
                    <i class="fas fa-eye fa-fw icon-preview"></i> <span class="sr-only"><?php _e('Preview', 'rd-downloads'); ?></span>
                </a>
                <!-- if has linked to other downloads, maybe display link to edit -->
                <# if (data.isLinkedDownloadsData === true) { #>
                    <span title="<?php esc_attr_e('This file is linked with other downloads data.', 'rd-downloads'); ?>">
                        <# if (data.editUrl) { #>
                            <a href="{{{data.editUrl}}}" target="editDownloads">
                        <# } #>
                        <i class="fas fa-link icon-linked-downloads"></i> <span class="sr-only"><?php _e('This file is linked with other downloads data.', 'rd-downloads'); ?></span>
                        <# if (data.editUrl) { #>
                            </a>
                        <# } #>
                    </span>
                <# } #>
                <!-- if deletable, link to ajax delete file -->
                <#if (data.isDeletable === true) { #>
                    <a class="delete-file-link" title="<?php esc_attr_e('Delete this file', 'rd-downloads'); ?>" onclick="return rdDownloadsAjaxDeleteFile('{{data.previousTargetEscaped}}', '{{{data.id}}}');">
                        <i class="fas fa-times"></i> <span class="sr-only"><?php _e('Delete this file', 'rd-downloads'); ?></span>
                    </a>
                <# } #>
            <# } else { #>
                <!-- this is folder -->
                <a onclick="return rdDownloadsAjaxFileBrowser('{{data.previousTargetEscaped}}', '{{{data.id}}}');"><i class="fas fa-folder icon-folder"></i> {{{data.filename}}}</a>
            <# } #>
        </li>
    </script><!-- multi line template that contain <#if#> with <template> element is not working. use script element instead. -->
</div><!--.wrap-->
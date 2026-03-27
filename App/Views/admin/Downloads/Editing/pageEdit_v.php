<?php
/**
 * Edit download views file.
 * 
 * @package rundiz-downloads
 * 
 * phpcs:disable Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace
 */


if (!defined('ABSPATH')) {
    exit();
}
?>
<div class="wrap">
    <h1><?php if (isset($page_heading1)) {echo esc_html($page_heading1);} ?></h1>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php echo esc_attr($form_result_class); ?> notice is-dismissible">
        <p>
            <strong><?php echo wp_kses_post($form_result_msg); ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'rundiz-downloads'); ?></span></button>
    </div>
    <?php } ?> 
    <div class="rundiz-downloads-form-result-placeholder"></div>

    <form id="rundiz-downloads-edit-form" class="rundiz-downloads-edit-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field(); ?> 
        <input id="download_id" type="hidden" name="download_id" value="<?php if (isset($download_id)) {echo esc_attr($download_id);} ?>">

        <div id="titlediv" class="rundiz-downloads-title-div">
            <input id="title" type="text" name="download_name" value="<?php if (isset($download_name)) {echo esc_attr($download_name);} ?>" maxlength="200" placeholder="<?php esc_attr_e('Downloads name', 'rundiz-downloads'); ?>">
        </div>
        <table class="form-table">
            <tbody>
                <tr>
                    <th><label for="download_url"><?php esc_html_e('Download URL', 'rundiz-downloads'); ?></label></th>
                    <td>
                        <input id="download_type" type="hidden" name="download_type" value="<?php if (isset($download_type)) {echo esc_attr($download_type);} ?>">
                        <input id="download_url" class="rundiz-downloads-input-full" type="url" name="download_url" value="<?php if (isset($download_url)) {echo esc_attr($download_url);} ?>" maxlength="300" required="">
                        <input id="download_related_path" type="hidden" name="download_related_path" value="<?php if (isset($download_related_path)) {echo esc_attr($download_related_path);} ?>">
                        <input id="download_size" type="hidden" name="download_size" value="<?php if (isset($download_size)) {echo esc_attr($download_size);} ?>">
                        <div id="download-size-and-preview" class="download-size-and-preview"><?php 
                        if (isset($download_size)) {
                            echo esc_html(size_format($download_size));
                        }
                        if (isset($download_url)) {
                            echo ' ';
                            echo '<a href="' . esc_attr($download_url) . '" target="preview-file" title="' . esc_attr__('Preview', 'rundiz-downloads') . '"><i class="fas fa-eye fa-fw rundiz-downloads-icon-preview"></i> <span class="sr-only">' . esc_html__('Preview', 'rundiz-downloads') . '</span></a>';
                        }
                        ?></div>
                        <p class="description"><?php esc_html_e('Please open the help tab to see help message.', 'rundiz-downloads'); ?></p>
                        <hr>
                        <div class="rundiz-downloads-form-type-local-file-browser rundiz-downloads-dropzone"></div>
                        <div class="rundiz-downloads-local-buttons">
                            <div class="rundiz-downloads-file-upload-button button">
                                <span><i class="fas fa-upload icon-upload"></i> <?php esc_html_e('Upload new file', 'rundiz-downloads'); ?></span>
                                <input id="rundiz-downloads-local-input-file" type="file">
                            </div>
                            <button class="rundiz-downloads-reload-button button" type="button" onclick="rdDownloadsAjaxFileBrowser();"><i class="fas fa-sync-alt icon-reload"></i> <?php esc_html_e('Reload', 'rundiz-downloads'); ?></button>
                        </div><!--.rundiz-downloads-local-buttons-->
                        <p class="description"><?php 
                        /* translators: %s: Max upload size. */
                        printf(esc_html__('Maximum upload file size: %s.', 'rundiz-downloads'), esc_html(size_format(wp_max_upload_size()))); 
                        ?></p>
                    </td>
                </tr>
                <tr class="rundiz-downloads-opt_download_version">
                    <th><label for="opt_download_version"><?php esc_html_e('Version', 'rundiz-downloads'); ?></label></th>
                    <td>
                        <input id="opt_download_version" class="regular-text" type="text" name="opt_download_version" value="<?php if (isset($opt_download_version)) {echo esc_attr($opt_download_version);} ?>" maxlength="50">
                        <p class="description">
                            <?php 
                            printf(
                                /* translators: %s: example version number. 1.0.0 */
                                esc_html__('The version number of this download. Example: %s', 'rundiz-downloads'), 
                                '1.2.3'
                            ); 
                            ?>
                        </p>
                    </td>
                </tr>
                <tr class="rundiz-downloads-opt_download_version_range<?php if (isset($download_type) && strval($download_type) !== '1') {echo ' hidden';} ?>">
                    <th><label for="opt_download_version_range"><?php esc_html_e('Version range', 'rundiz-downloads'); ?></label></th>
                    <td>
                        <input id="opt_download_version_range" class="regular-text" type="text" name="opt_download_version_range" value="<?php if (isset($opt_download_version_range)) {echo esc_attr($opt_download_version_range);} ?>" maxlength="50">
                        <p class="description">
                            <?php 
                            esc_html_e('The version range to get update.', 'rundiz-downloads');
                            echo ' ';
                            printf(
                                /* translators: %1$s: Open link, %2$s: Close link. */
                                esc_html__('We use the same versions and constraints as %1$scomposer%2$s.', 'rundiz-downloads'),
                                '<a href="https://getcomposer.org/doc/articles/versions.md" target="composer_versionsconstraints">',
                                '</a>'
                            ); 
                            echo ' ';
                            /* translators: %s: Example version constraints. >=your current version */
                            printf(esc_html__('Default is %s', 'rundiz-downloads'), '<code>&gt;=your current version</code>');
                            ?><br>
                            <?php esc_html_e('This is for GitHub download only.', 'rundiz-downloads'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="download_admin_comment"><?php esc_html_e('Notes', 'rundiz-downloads'); ?></label></th>
                    <td>
                        <textarea id="download_admin_comment" class="widefat" name="download_admin_comment" maxlength="1000" rows="3"><?php if (isset($download_admin_comment)) {echo esc_html($download_admin_comment);} ?></textarea>
                        <p class="description"><?php esc_html_e('Notes about this download, display to administrator only. HTML will be escaped.', 'rundiz-downloads'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="download_count"><?php esc_html_e('Download count', 'rundiz-downloads'); ?></label></th>
                    <td>
                        <input id="download_count" class="regular-text" type="number" name="download_count" value="<?php if (isset($download_count)) {echo esc_attr($download_count);} ?>" min="0" max="99999999999" step="1" placeholder="0">
                        <p class="description"><?php esc_html_e('The number of times this file has been downloaded.', 'rundiz-downloads'); ?></p>
                    </td>
                </tr>
                <tr class="rundiz-downloads-opt_force_download<?php if (isset($download_type) && strval($download_type) !== '0') {echo ' hidden';} ?>">
                    <th><label for="opt_force_download"><?php esc_html_e('Force download', 'rundiz-downloads'); ?></label></th>
                    <td>
                        <label><input type="radio" name="opt_force_download" value="1"<?php if (isset($opt_force_download) && strval($opt_force_download) === '1') {echo ' checked="checked"';} ?>><?php esc_html_e('Yes', 'rundiz-downloads'); ?></label> &nbsp;
                        <label><input type="radio" name="opt_force_download" value="0"<?php if (isset($opt_force_download) && strval($opt_force_download) === '0') {echo ' checked="checked"';} ?>><?php esc_html_e('No', 'rundiz-downloads'); ?></label> &nbsp;
                        <label>
                            <input type="radio" name="opt_force_download" value=""<?php if (!isset($opt_force_download) || (isset($opt_force_download) && strval($opt_force_download) === '')) {echo ' checked="checked"';} ?>>
                            <?php esc_html_e('Default', 'rundiz-downloads'); ?> 
                            <span class="description">(<?php
                            if (isset($rundiz_downloads_options['rdd_force_download']) && strval($rundiz_downloads_options['rdd_force_download']) === '1') {
                                $rundiz_downloads_pluginSettingsUse = __('Force download', 'rundiz-downloads');
                            } else {
                                $rundiz_downloads_pluginSettingsUse = __('redirect to file', 'rundiz-downloads');
                            }
                            /* translators: %s: Force download option value for main plugin setting. */
                            printf(esc_html__('The plugin setting is using %s.', 'rundiz-downloads'), esc_html($rundiz_downloads_pluginSettingsUse));
                            unset($rundiz_downloads_pluginSettingsUse);
                            ?>)</span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Choose yes and the file will use force download instead of redirect to it.', 'rundiz-downloads'); ?> 
                            <?php esc_html_e('Please open the help tab to see help message.', 'rundiz-downloads'); ?> 
                        </p>
                    </td>
                </tr>
                <tr class="rundiz-downloads-publish-data">
                    <th><label><?php esc_html_e('Publish data', 'rundiz-downloads'); ?></label></th>
                    <td>
                        <p><i class="fas fa-user"></i> <?php esc_html_e('Created by', 'rundiz-downloads'); ?>: <span class="create-by"><?php 
                        if (isset($user_id)) {
                            echo '<a href="' . esc_url(get_edit_user_link($user_id)) . '" target="editUser">';
                        } 
                        if (isset($display_name)) {
                            echo esc_html($display_name);
                        } 
                        if (isset($user_id)) {
                            echo '</a>';
                        } 
                        ?></span></p>
                        <p><i class="fas fa-calendar-alt"></i> <?php esc_html_e('Created on', 'rundiz-downloads'); ?>: <span class="create-on"><?php 
                        if (isset($download_create_gmt)) {
                            echo esc_html(RundizDownloads\App\Libraries\DateTime::displayDateTime($download_create_gmt));
                        } 
                        ?></span></p>
                        <p><i class="fas fa-calendar-alt"></i> <?php esc_html_e('Last update', 'rundiz-downloads'); ?>: <span class="last-update"><?php 
                        if (isset($download_update_gmt)) {
                            echo esc_html(RundizDownloads\App\Libraries\DateTime::displayDateTime($download_update_gmt));
                        } 
                        ?></span></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button id="submit" class="button button-primary rundiz-downloads-save-form-button" type="submit" name="submit" value="save"><?php esc_html_e('Save', 'rundiz-downloads'); ?></button>
        </p>
    </form><!--#rundiz-downloads-edit-form-->

    <!-- do not use HTML template tag because `wp.template()` will not work. -->
    <script type="text/html" id="tmpl-selected-download-file-size">
        {{{data.size}}} <a href="{{data.url}}" target="preview-file" title="<?php esc_attr_e('Preview', 'rundiz-downloads'); ?>"><i class="fas fa-eye fa-fw rundiz-downloads-icon-preview"></i> <span class="sr-only"><?php esc_html_e('Preview', 'rundiz-downloads'); ?></span></a>
    </script>

    <!-- do not use HTML template tag because `wp.template()` will not work. -->
    <script type="text/html" id="tmpl-file-browser-list-item">
        <li id="{{{data.id}}}" class="<# if (data.isFile === true) { #>item-file<# } else { #>item-folder<# } #>">
            <# if (data.isFile === true) { #>
                <!-- this is file -->
                <!-- link to select file -->
                <a onclick="rdDownloadsSelectLocalFile(this);" data-isdeletable="{{{data.isDeletable}}}" data-size="{{{data.readableFileSize}}}" data-url="{{{data.url}}}" data-relatedpath="{{{data.relatedPathEscaped}}}">
                    <i class="fas fa-file icon-file"></i> {{{data.filename}}} ({{{data.readableFileSize}}})
                </a>
                <!-- link to preview -->
                <a href="{{{data.url}}}" target="preview-file" title="<?php esc_attr_e('Preview', 'rundiz-downloads'); ?>">
                    <i class="fas fa-eye fa-fw icon-preview"></i> <span class="sr-only"><?php esc_html_e('Preview', 'rundiz-downloads'); ?></span>
                </a>
                <!-- if has linked to other downloads, maybe display link to edit -->
                <# if (data.isLinkedDownloadsData === true) { #>
                    <span title="<?php esc_attr_e('This file is linked with other downloads data.', 'rundiz-downloads'); ?>">
                        <# if (data.editUrl) { #>
                            <a href="{{{data.editUrl}}}" target="editDownloads">
                        <# } #>
                        <i class="fas fa-link icon-linked-downloads"></i> <span class="sr-only"><?php esc_html_e('This file is linked with other downloads data.', 'rundiz-downloads'); ?></span>
                        <# if (data.editUrl) { #>
                            </a>
                        <# } #>
                    </span>
                <# } #>
                <!-- if deletable, link to ajax delete file -->
                <#if (data.isDeletable === true) { #>
                    <a class="delete-file-link" title="<?php esc_attr_e('Delete this file', 'rundiz-downloads'); ?>" onclick="return rdDownloadsAjaxDeleteFile('{{data.previousTargetEscaped}}', '{{{data.id}}}');">
                        <i class="fas fa-times"></i> <span class="sr-only"><?php esc_html_e('Delete this file', 'rundiz-downloads'); ?></span>
                    </a>
                <# } #>
            <# } else { #>
                <!-- this is folder -->
                <a onclick="return rdDownloadsAjaxFileBrowser('{{data.previousTargetEscaped}}', '{{{data.id}}}');"><i class="fas fa-folder icon-folder"></i> {{{data.filename}}}</a>
            <# } #>
        </li>
    </script>
</div><!--.wrap-->
<?php
/**
 * Rundiz Downloads management settings.
 *
 * @package rundiz-downloads
 */


if (!defined('ABSPATH')) {
    exit();
}


// html element placeholder for convert from shortcode. -----------------------------------------------------
$rundiz_downloads_ElementPlaceholders = new \RundizDownloads\App\Libraries\ElementPlaceholders();

// text placeholders for design help.
$rundiz_downloads_placeholders = $rundiz_downloads_ElementPlaceholders->textPlaceholders();
$rundiz_downloads_arrayKeys = array_keys($rundiz_downloads_placeholders);
$rundiz_downloads_lastArrayKey = array_pop($rundiz_downloads_arrayKeys);
unset($rundiz_downloads_arrayKeys);
$rundiz_downloads_txtPlaceholders = '';
foreach ($rundiz_downloads_placeholders as $rundiz_downloads_placeholder => $rundiz_downloads_translated) {
    $rundiz_downloads_txtPlaceholders .= '<code>{{' . $rundiz_downloads_placeholder . '}}</code> =&gt; ' . $rundiz_downloads_translated;
    if ($rundiz_downloads_placeholder !== $rundiz_downloads_lastArrayKey) {
        $rundiz_downloads_txtPlaceholders .= '<br>' . PHP_EOL;
    }
}
unset($rundiz_downloads_lastArrayKey, $rundiz_downloads_placeholder, $rundiz_downloads_placeholders, $rundiz_downloads_translated);

// db placeholders for design help.
$rundiz_downloads_placeholders = $rundiz_downloads_ElementPlaceholders->dbPlaceholders();
$rundiz_downloads_arrayKeys = array_keys($rundiz_downloads_placeholders);
$rundiz_downloads_lastArrayKey = array_pop($rundiz_downloads_arrayKeys);
unset($rundiz_downloads_arrayKeys);
$rundiz_downloads_dbPlaceholders = '';
foreach ($rundiz_downloads_placeholders as $rundiz_downloads_placeholder) {
    $rundiz_downloads_dbPlaceholders .= '<code>{{' . $rundiz_downloads_placeholder . '}}</code>';
    if ($rundiz_downloads_placeholder !== $rundiz_downloads_lastArrayKey) {
        $rundiz_downloads_dbPlaceholders .= '<br>' . PHP_EOL;
    }
}
unset($rundiz_downloads_lastArrayKey, $rundiz_downloads_placeholder, $rundiz_downloads_placeholders);
// end html element placeholder for convert from shortcode. -------------------------------------------------

// available shortcode attributes for design help.
$rundiz_downloads_ShortcodeRdDownloads = new \RundizDownloads\App\Libraries\ShortcodeRdDownloads();
$rundiz_downloads_attributes = $rundiz_downloads_ShortcodeRdDownloads->availableAttributes();
$rundiz_downloads_arrayKeys = array_keys($rundiz_downloads_attributes);
$rundiz_downloads_lastArrayKey = array_pop($rundiz_downloads_arrayKeys);
unset($rundiz_downloads_arrayKeys);
$rundiz_downloads_availableShortcodeAttributes = '';
foreach ($rundiz_downloads_attributes as $rundiz_downloads_attribute => $rundiz_downloads_item) {
    $rundiz_downloads_availableShortcodeAttributes .= '<code>' . $rundiz_downloads_attribute . '</code>';
    if (isset($rundiz_downloads_item['helpmsg'])) {
        $rundiz_downloads_availableShortcodeAttributes .= ' =&gt; ' . $rundiz_downloads_item['helpmsg'];
    }
    if ($rundiz_downloads_attribute !== $rundiz_downloads_lastArrayKey) {
        $rundiz_downloads_availableShortcodeAttributes .= '<br>' . PHP_EOL;
    }
}
unset($rundiz_downloads_lastArrayKey, $rundiz_downloads_attribute, $rundiz_downloads_attributes, $rundiz_downloads_item);
unset($rundiz_downloads_ShortcodeRdDownloads);

// design help -----------------------------------------------------------------------------------------------
$rundiz_downloads_designDefaultValue = $rundiz_downloads_ElementPlaceholders->defaultDownloadHtml();
$rundiz_downloads_designHelp = '<p>' . PHP_EOL;
/* translators: %s: Example shortcode. */
$rundiz_downloads_designHelp .= sprintf(__('The HTML element for replace the %s shortcode.', 'rundiz-downloads'), '<code>[rddownloads]</code>');
/* translators: %s: Default html elements that will be convert shortcode to this. */
$rundiz_downloads_designHelp .= sprintf(__('Default value is %s.', 'rundiz-downloads'), '<code>' . esc_html($rundiz_downloads_designDefaultValue) . '</code>') . PHP_EOL;
$rundiz_downloads_designHelp .= '</p>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<h3>' . __('Available shortcode attributes.', 'rundiz-downloads') . '</h3>' . PHP_EOL;
/* translators: %s: id attribute */
$rundiz_downloads_designHelp .= '<p>' . sprintf(__('Set one or more of these attributes into shortcode to match condition in the template and display its value. The %s attribute is required.', 'rundiz-downloads'), '<code>id</code>') . '</p>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<p>' . PHP_EOL;
$rundiz_downloads_designHelp .= $rundiz_downloads_availableShortcodeAttributes . PHP_EOL;
$rundiz_downloads_designHelp .= '</p>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<h3>' . __('Placeholders for the text replacement.', 'rundiz-downloads') . '</h3>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<p>' . PHP_EOL;
$rundiz_downloads_designHelp .= $rundiz_downloads_txtPlaceholders;
$rundiz_downloads_designHelp .= '</p>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<h3>' . __('Placeholders for DB fields replacement.', 'rundiz-downloads') . '</h3>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<p>' . PHP_EOL;
$rundiz_downloads_designHelp .= $rundiz_downloads_dbPlaceholders;
$rundiz_downloads_designHelp .= '</p>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<h3>' . __('Conditional template tag.', 'rundiz-downloads') . '</h3>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<p>' . PHP_EOL;
$rundiz_downloads_designHelp .= __('You can use conditional template tag to check that shortcode attribute was set. Example:', 'rundiz-downloads') . PHP_EOL;
$rundiz_downloads_designHelp .= '</p>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<pre class="rd-settings-preformat-code">' . PHP_EOL;
$rundiz_downloads_designHelp .= esc_html('{if display_size}
    {{txt_file_size}}: {{download_size}}
{endif}');
$rundiz_downloads_designHelp .= '</pre>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<p>' . PHP_EOL;
$rundiz_downloads_designHelp .= __('You can also use conditional template tag to check that DB value was set. Example:', 'rundiz-downloads') . PHP_EOL;
$rundiz_downloads_designHelp .= '</p>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<pre class="rd-settings-preformat-code">' . PHP_EOL;
$rundiz_downloads_designHelp .= esc_html('{if download_github_name}
    Download on GitHub ({{download_github_name}}).
{endif}');
$rundiz_downloads_designHelp .= '</pre>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<p>' . PHP_EOL;
$rundiz_downloads_designHelp .= __('More advance example:', 'rundiz-downloads') . PHP_EOL;
$rundiz_downloads_designHelp .= '</p>' . PHP_EOL;
$rundiz_downloads_designHelp .= '<pre class="rd-settings-preformat-code">' . PHP_EOL;
$rundiz_downloads_designHelp .= esc_html('<div class="rundiz-downloads-block">');
$rundiz_downloads_designHelp .= esc_html($rundiz_downloads_designDefaultValue);
$rundiz_downloads_designHelp .= esc_html('
{if display_download_count} ({{txt_total_download}}: {{download_count}}){endif}
<br>{{txt_download_name}}: {{download_name}}
{if display_download_version}<br>{{txt_version}}: {{opt_download_version}}{endif}
{if download_github_name}<br>{{txt_github_name}}: {{download_github_name}}{endif}
{if display_file_name}<br>{{txt_file_name}}: {{download_file_name}}{endif}
{if display_size}<br>{{txt_file_size}}: {{download_size}}{endif}
{if display_create_date}<br>{{txt_create_on}}: {{download_create}} ({{download_create_gmt}}){endif}
{if display_last_update}<br>{{txt_last_update}}: {{download_update}} ({{download_update_gmt}}){endif}
');
$rundiz_downloads_designHelp .= esc_html('</div>');
$rundiz_downloads_designHelp .= '</pre>' . PHP_EOL;
unset($rundiz_downloads_availableShortcodeAttributes, $rundiz_downloads_dbPlaceholders, $rundiz_downloads_ElementPlaceholders, $rundiz_downloads_txtPlaceholders);
// end design help ------------------------------------------------------------------------------------------


// GitHub help -----------------------------------------------------------------------------------------------
$rundiz_downloads_githubAutoUpdateHelp = __('Choose how the auto update works. Auto update on every release and commit can make your server slow, if you choose every release then it will work less.', 'rundiz-downloads') . '<br>';
/* translators: %1$s: Client ID, %2$s: Client Secret. */
$rundiz_downloads_githubAutoUpdateHelp .= sprintf(__('To make the auto update works, please follow the instruction below to get %1$s and %2$s', 'rundiz-downloads'), '<strong>' . __('Client ID', 'rundiz-downloads') . '</strong>', '<strong>' . __('Client Secret', 'rundiz-downloads') . '</strong>');

$rundiz_downloads_githubOAuthHelp = '<h3>' . __('GitHub OAuth', 'rundiz-downloads') . '</h3>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<p>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= __('This plugin use GitHub OAuth to auto update, auto correct repository URL. This is a lot easier for manage many repositories webhook and support multiple user\'s repositories.', 'rundiz-downloads') . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<br>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= __('To make these functions work, you have to register an OAuth application on GitHub for your website.', 'rundiz-downloads') . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '</p>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<h4>' . __('Register OAuth application', 'rundiz-downloads') . '</h4>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<ol>' . PHP_EOL;
/* translators: %1$s: Open link, %2$s: Close link. */
$rundiz_downloads_githubOAuthHelp .= '<li>' . sprintf(__('Go to %1$sGitHub.com%2$s website.', 'rundiz-downloads'), '<a href="https://github.com/" target="github">', '</a>') . '</li>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<li>' . __('Register an application', 'rundiz-downloads') . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<ul class="rd-settings-ul">' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<li>' . __('If you want to register an application for your organization, please go to your organization &gt; Settings &gt; Developer settings &gt; OAtuh apps and click on Register an application.', 'rundiz-downloads') . '</li>' . PHP_EOL;
/* translators: %1$s: Open link, %2$s: Close link. */
$rundiz_downloads_githubOAuthHelp .= '<li>' . sprintf(__('If you want to register an application for yourself, please go to your Settings &gt; Developer settings &gt; %1$sOAtuh apps%2$s and click on New OAuth app.', 'rundiz-downloads'), '<a href="https://github.com/settings/developers" target="github_usersettings">', '</a>') . '</li>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '</ul>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '</li>' . PHP_EOL;
/* translators: %s: Home URL. */
$rundiz_downloads_githubOAuthHelp .= '<li>' . sprintf(__('Enter your application data here and set %s for Homepage URL and Authorization callback URL.', 'rundiz-downloads'), '<strong>' . get_home_url() . '</strong>') . '</li>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<li>' . __('Click on Register application button.', 'rundiz-downloads') . '</li>' . PHP_EOL;
/* translators: %1$s: Client ID, %2$s: Client Secret. */
$rundiz_downloads_githubOAuthHelp .= '<li>' . sprintf(__('Copy %1$s and %2$s to the form field above. You may add your logo in the OAuth application settings page.', 'rundiz-downloads'), '<strong>' . __('Client ID', 'rundiz-downloads') . '</strong>', '<strong>' . __('Client Secret', 'rundiz-downloads') . '</strong>') . '</li>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '</ol>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<h4>' . __('Connect users with their GitHub', 'rundiz-downloads') . '</h4>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<p>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= __('Your users who want to add the download data and using GitHub auto update, auto correct repository URL features must connect this website with their GitHub.', 'rundiz-downloads') . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '</p>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<ol>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '<li>' . __('Complete all the steps above and GitHub OAuth link will be appears.', 'rundiz-downloads') . '</li>' . PHP_EOL;
/* translators: %1$s: Open link, %2$s: Close link. */
$rundiz_downloads_githubOAuthHelp .= '<li>' . sprintf(__('Click on %1$sGitHub OAuth%2$s menu and follow instruction.', 'rundiz-downloads'), '<a href="' . admin_url('admin.php?page=' . \RundizDownloads\App\Controllers\Admin\Downloads\GithubOAuth::MENU_SLUG) . '">', '</a>') . '</li>' . PHP_EOL;
$rundiz_downloads_githubOAuthHelp .= '</ol>' . PHP_EOL;
// end GitHub help ------------------------------------------------------------------------------------------

return [
    'tab_style' => 'vertical',
    'setting_tabs' => [
        [
            'icon' => 'fas fa-cogs',
            'title' => __('General', 'rundiz-downloads'),
            'fields' => [
                [
                    'options' => [
                        [
                            'default' => '',
                            'description' => __('Check this to use force download instead of redirect to file. (This will work with local file only.)', 'rundiz-downloads') . ' ' . __('Default is no.', 'rundiz-downloads'),
                            'id' => 'rdd_force_download',
                            'title' => __('Yes', 'rundiz-downloads'),
                            'value' => '1',
                        ],
                    ],
                    'title' => __('Force download', 'rundiz-downloads'),
                    'type' => 'checkbox',
                ],
                [
                    'content' => '<button id="rundiz-downloads-settings-clear-cache" class="button" type="button">' . __('Clear cache', 'rundiz-downloads') . '</button><br>' .
                        __('If something seems not up-to-date, please try to clear the cache first. This will be clear all plugin\'s cache.', 'rundiz-downloads'),
                    'title' => __('Cache', 'rundiz-downloads'),
                    'type' => 'html',
                ],
            ],// fields
        ],// end general tab.
        [
            'icon' => 'fas fa-diagnoses',
            'title' => __('Anti robots', 'rundiz-downloads'),
            'fields' => [
                [
                    'default' => '',
                    'description' => '<p class="description">' . __('Anti bot form will display the form field for user to fill and another form field for bot to fill.', 'rundiz-downloads') . '</p>',
                    'id' => 'rdd_use_antibotfield',
                    'options' => [
                        '' => __('Do not use', 'rundiz-downloads') . ' (' . __('Default', 'rundiz-downloads') . ')',
                        'yes' => __('Use anti bot form field', 'rundiz-downloads'),
                    ],
                    'title' => __('Use anti bot form', 'rundiz-downloads'),
                    'type' => 'select',
                ],
                [
                    'default' => "bot\nyahoo! slurp",
                    'description' => __('User agent that contain text in one of this will be blocked. One per line, case insensitive.', 'rundiz-downloads'),
                    'id' => 'rdd_block_ua',
                    'input_attributes' => [
                        'rows' => 5,
                    ],
                    'title' => __('Block user agents', 'rundiz-downloads'),
                    'type' => 'textarea',
                ],
            ],// fields
        ],// end anti bot tab.
        [
            'icon' => 'fab fa-github',
            'title' => __('GitHub', 'rundiz-downloads'),
            'fields' => [
                [
                    'default' => 'release',
                    'description' => $rundiz_downloads_githubAutoUpdateHelp,
                    'id' => 'rdd_github_auto_update',
                    'options' => [
                        '' => __('Do not auto update', 'rundiz-downloads'),
                        'release' => __('Every release', 'rundiz-downloads') . ' (' . __('Default', 'rundiz-downloads') . ')',
                        'release+commit' => __('Every release and commit', 'rundiz-downloads'),
                    ],
                    'title' => __('Auto update', 'rundiz-downloads'),
                    'type' => 'select',
                ],
                [
                    'default' => '',
                    'id' => 'rdd_github_client_id',
                    'input_attributes' => [
                        'autocomplete' => 'off',
                    ],
                    'title' => __('Client ID', 'rundiz-downloads'),
                    'type' => 'text',
                ],
                [
                    'default' => '',
                    'id' => 'rdd_github_client_secret',
                    'input_attributes' => [
                        'autocomplete' => 'off',
                    ],
                    'title' => __('Client Secret', 'rundiz-downloads'),
                    'type' => 'text',
                ],
                [
                    'content' => $rundiz_downloads_githubOAuthHelp,
                    'title' => '',
                    'type' => 'html',
                ],
                // GitHub token is for auto correct URL (get latest repository data by version range), auto update from GitHub push event.
                // GitHub secret is for auto update from GitHub push event via webhook.
                // GitHub secret must be add to the repository by user's action after connected GitHub OAuth to make auto update work.
            ],// fields
        ],// end GitHub tab.
        [
            'icon' => 'fas fa-chart-bar',
            'title' => __('Logs/statistic', 'rundiz-downloads'),
            'fields' => [
                [
                    'options' => [
                        [
                            'default' => '1',
                            'description' => __('Check this to automatically delete old logs.', 'rundiz-downloads') . ' ' . __('Default is yes.', 'rundiz-downloads'),
                            'id' => 'rdd_auto_delete_logs',
                            'title' => __('Yes', 'rundiz-downloads'),
                            'value' => '1',
                        ],
                    ],
                    'title' => __('Auto delete logs', 'rundiz-downloads'),
                    'type' => 'checkbox',
                ],
                [
                    'default' => '90',
                    /* translators: %s is Auto delete logs option */
                    'description' => sprintf(__('Auto delete logs after specific days. This will be skipped if %s was not checked.', 'rundiz-downloads'), '<strong>' . __('Auto delete logs', 'rundiz-downloads') . '</strong>') . ' ' . __('Default is 90.', 'rundiz-downloads'),
                    'id' => 'rdd_auto_delete_logs_days',
                    'input_attributes' => [
                        'max' => 365,
                        'min' => 1,
                        'step' => 1,
                    ],
                    'title' => __('Days limit', 'rundiz-downloads'),
                    'type' => 'number',
                ],
                [
                    'options' => [
                        [
                            'default' => '1',
                            'description' => __('Check this to logs admin actions such as add, update, delete downloads.', 'rundiz-downloads') . ' ' . __('Default is yes.', 'rundiz-downloads'),
                            'id' => 'rdd_admin_logs',
                            'title' => __('Yes', 'rundiz-downloads'),
                            'value' => '1',
                        ],
                    ],
                    'title' => __('Admin actions', 'rundiz-downloads'),
                    'type' => 'checkbox',
                ],
            ],// fields
        ],// end logs/stat tab.
        [
            'icon' => 'fas fa-paint-brush',
            'title' => __('Design', 'rundiz-downloads'),
            'fields' => [
                [
                    'default' => $rundiz_downloads_designDefaultValue,
                    'id' => 'rdd_download_element',
                    // mode refer from https://github.com/ajaxorg/ace/blob/master/lib/ace/ext/modelist.js#L53
                    'mode' => 'html',
                    'title' => __('Downloads element', 'rundiz-downloads'),
                    'type' => 'code_editor',
                ],
                [
                    'content' => $rundiz_downloads_designHelp,
                    'title' => '',
                    'type' => 'html',
                ],
            ],// fields
        ],// end customize element tab.
    ],// setting_tabs
];

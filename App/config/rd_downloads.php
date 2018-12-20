<?php
/**
 * Rundiz Downloads management settings.
 * 
 * @package rd-downloads
 */

// html element placeholder for convert from shortcode. -----------------------------------------------------
$ElementPlaceholders = new \RdDownloads\App\Libraries\ElementPlaceholders();

$placeholders = $ElementPlaceholders->textPlaceholders();
$arrayKeys = array_keys($placeholders);
$lastArrayKey = array_pop($arrayKeys);
unset($arrayKeys);
$txtPlaceholders = '';
foreach ($placeholders as $placeholder => $translated) {
    $txtPlaceholders .= '<code>{{' . $placeholder . '}}</code> =&gt; ' . $translated;
    if ($placeholder != $lastArrayKey) {
        $txtPlaceholders .= '<br>' . PHP_EOL;
    }
}
unset($lastArrayKey, $placeholder, $placeholders, $translated);

$placeholders = $ElementPlaceholders->dbPlaceholders();
$arrayKeys = array_keys($placeholders);
$lastArrayKey = array_pop($arrayKeys);
unset($arrayKeys);
$dbPlaceholders = '';
foreach ($placeholders as $placeholder) {
    $dbPlaceholders .= '<code>{{' . $placeholder . '}}</code>';
    if ($placeholder != $lastArrayKey) {
        $dbPlaceholders .= '<br>' . PHP_EOL;
    }
}
unset($lastArrayKey, $placeholder, $placeholders);
// end html element placeholder for convert from shortcode. -------------------------------------------------

$ShortcodeRdDownloads = new \RdDownloads\App\Libraries\ShortcodeRdDownloads();
$attributes = $ShortcodeRdDownloads->availableAttributes();
$arrayKeys = array_keys($attributes);
$lastArrayKey = array_pop($arrayKeys);
unset($arrayKeys);
$availableShortcodeAttributes = '';
foreach ($attributes as $attribute => $item) {
    $availableShortcodeAttributes .= '<code>' . $attribute . '</code>';
    if (isset($item['helpmsg'])) {
        $availableShortcodeAttributes .= ' =&gt; ' . $item['helpmsg'];
    }
    if ($attribute != $lastArrayKey) {
        $availableShortcodeAttributes .= '<br>' . PHP_EOL;
    }
}
unset($lastArrayKey, $attribute, $attributes, $item);
unset($ShortcodeRdDownloads);

// design help -----------------------------------------------------------------------------------------------
$designDefaultValue = $ElementPlaceholders->defaultDownloadHtml();
$designHelp = '<p>' . PHP_EOL;
/* translators: %s: Example shortcode. */
$designHelp .= sprintf(__('The HTML element for replace the %s shortcode.', 'rd-downloads'), '<code>[rddownloads]</code>');
/* translators: %s: Default html elements that will be convert shortcode to this. */
$designHelp .= sprintf(__('Default value is %s.', 'rd-downloads'), '<code>' . esc_html($designDefaultValue) . '</code>') . PHP_EOL;
$designHelp .= '</p>' . PHP_EOL;
$designHelp .= '<h3>' . __('Available shortcode attributes.', 'rd-downloads') . '</h3>' . PHP_EOL;
/* translator: %s: id attribute */
$designHelp .= '<p>' . sprintf(__('Set one or more of these attributes into shortcode to match condition in the template and display its value. The %s attribute is required.', 'rd-downloads'), '<code>id</code>') . '</p>' . PHP_EOL;
$designHelp .= '<p>' . PHP_EOL;
$designHelp .=  $availableShortcodeAttributes . PHP_EOL;
$designHelp .= '</p>' . PHP_EOL;
$designHelp .= '<h3>' . __('Placeholders for the text replacement.', 'rd-downloads') . '</h3>' . PHP_EOL;
$designHelp .= '<p>' . PHP_EOL;
$designHelp .=  $txtPlaceholders;
$designHelp .= '</p>' . PHP_EOL;
$designHelp .= '<h3>' . __('Placeholders for DB fields replacement.', 'rd-downloads') . '</h3>' . PHP_EOL;
$designHelp .= '<p>' . PHP_EOL;
$designHelp .= $dbPlaceholders;
$designHelp .= '</p>' . PHP_EOL;
$designHelp .= '<h3>' . __('Conditional template tag.', 'rd-downloads') . '</h3>' . PHP_EOL;
$designHelp .= '<p>' . PHP_EOL;
$designHelp .= __('You can use conditional template tag to check that shortcode attribute was set. Example:', 'rd-downloads') . PHP_EOL;
$designHelp .= '</p>' . PHP_EOL;
$designHelp .= '<pre class="rd-settings-preformat-code">' . PHP_EOL;
$designHelp .= esc_html('{if display_size}
    {{txt_file_size}}: {{download_size}}
{endif}');
$designHelp .= '</pre>' . PHP_EOL;
$designHelp .= '<p>' . PHP_EOL;
$designHelp .= __('You can also use conditional template tag to check that DB value was set. Example:', 'rd-downloads') . PHP_EOL;
$designHelp .= '</p>' . PHP_EOL;
$designHelp .= '<pre class="rd-settings-preformat-code">' . PHP_EOL;
$designHelp .= esc_html('{if download_github_name}
    Download on GitHub ({{download_github_name}}).
{endif}');
$designHelp .= '</pre>' . PHP_EOL;
$designHelp .= '<p>' . PHP_EOL;
$designHelp .= __('More advance example:', 'rd-downloads') . PHP_EOL;
$designHelp .= '</p>' . PHP_EOL;
$designHelp .= '<pre class="rd-settings-preformat-code">' . PHP_EOL;
$designHelp .= esc_html('<div class="rd-downloads-block">');
$designHelp .= esc_html($designDefaultValue);
$designHelp .= esc_html('
{if display_download_count} ({{txt_total_download}}: {{download_count}}){endif}
<br>{{txt_download_name}}: {{download_name}}
{if download_github_name}<br>{{txt_github_name}}: {{download_github_name}}{endif}
{if display_file_name}<br>{{txt_file_name}}: {{download_file_name}}{endif}
{if display_size}<br>{{txt_file_size}}: {{download_size}}{endif}
{if display_create_date}<br>{{txt_create_on}}: {{download_create}} ({{download_create_gmt}}){endif}
{if display_last_update}<br>{{txt_last_update}}: {{download_update}} ({{download_update_gmt}}){endif}
');
$designHelp .= esc_html('</div>');
$designHelp .= '</pre>' . PHP_EOL;
unset($availableShortcodeAttributes, $dbPlaceholders, $ElementPlaceholders, $txtPlaceholders);
// end design help ------------------------------------------------------------------------------------------

$githubSecretHelp = '<p><strong>' . __('To use GitHub auto update', 'rd-downloads') . '</strong></p>
    <ul class="rd-settings-ul">
        <li>' . sprintf(__('Go to your %1$sGitHub%2$s repository.', 'rd-downloads'), '<a href="https://github.com/" target="github">', '</a>') . '</li>
        <li>' . __('Go to Settings &gt; Webhooks.', 'rd-downloads') . '</li>
        <li>' . sprintf(
            __('If you didn\'t created Webhooks yet, click on %1$s button. If you want to change the secret, click on %2$s button.', 'rd-downloads'), 
            '<strong>' . __('Add webhook', 'rd-downloads') . '</strong>', 
            '<strong>' . __('Edit', 'rd-downloads') . '</strong>'
        ) . '</li>
        <li>' . sprintf(__('Enter %s for payload URL.', 'rd-downloads'), '<code>' . add_query_arg(['pagename' => 'rddownloads_github_autoupdate'], home_url()) . '</code>') . '</li>
        <li>' . sprintf(__('Content type is %s.', 'rd-downloads'), '<strong>application/json</strong>') . '</li>
        <li>' . __('On Secret field, enter the secret generated from here.', 'rd-downloads') . '</li>
        <li>' . sprintf(__('On events to trigger this webhook, choose %s.', 'rd-downloads'), '<strong>' . __('Just the push event', 'rd-downloads') . '</strong>') . '</li>
        <li>' . sprintf(__('Check on %s checkbox and save.', 'rd-downloads'), '<strong>' . __('Active', 'rd-downloads') . '</strong>') . '</li>
    </ul>
' . PHP_EOL;

$githubTokenHelp = '<p><strong>' . __('To get GitHub token', 'rd-downloads') . '</strong></p>
    <ul class="rd-settings-ul">
        <li>' . sprintf(__('Go to your %1$sGitHub%2$s website.', 'rd-downloads'), '<a href="https://github.com/" target="github">', '</a>') . '</li>
        <li>' . __('Click at your icon on the right of the top bar, the menu will be appear.', 'rd-downloads') . '</li>
        <li>' . sprintf(__('Go to %1$sSettings &gt; Developer settings &gt; Personal access tokens%2$s page.', 'rd-downloads'), '<a href="https://github.com/settings/tokens" target="githubTOken">', '</a>') . '</li>
        <li>' . sprintf(__('Click on %s button.', 'rd-downloads'), '<strong>' . __('Generate new token', 'rd-downloads') . '</strong>') . '</li>
        <li>' . __('Enter Token description.', 'rd-downloads') . '</li>
        <li>' . sprintf(__('Check for these scopes. %s', 'rd-downloads'), 'repo, read:org, read:public_key, read:repo_hook, read:user, user:email, read:gpg_key') . '</li>
        <li>' . sprintf(__('Click on %s button.', 'rd-downloads'), '<strong>' . __('Generate token', 'rd-downloads') . '</strong>') . '</li>
        <li>' . __('Copy your personal token and paste above field then save changes before you won\'t be able to see it again.', 'rd-downloads') . '</li>
    </ul>
' . PHP_EOL;

return [
    'tab_style' => 'vertical',
    'setting_tabs' => [
        [
            'icon' => 'fas fa-cogs',
            'title' => __('General', 'rd-downloads'),
            'fields' => [
                [
                    'options' => [
                        [
                            'default' => '',
                            'description' => __('Check this to use force download instead of redirect to file. (This will work with local file only.)', 'rd-downloads') . ' ' . __('Default is no.', 'rd-downloads'),
                            'id' => 'rdd_force_download',
                            'title' => __('Yes', 'rd-downloads'),
                            'value' => '1',
                        ],
                    ],
                    'title' => __('Force download', 'rd-downloads'),
                    'type' => 'checkbox',
                ],
                [
                    'options' => [
                        [
                            'default' => '',
                            'description' => __('Captcha is human validation that can help prevent bot, check this to use the captcha.', 'rd-downloads') . ' ' . __('Default is no.', 'rd-downloads'),
                            'id' => 'rdd_use_captcha',
                            'title' => __('Yes', 'rd-downloads'),
                            'value' => '1',
                        ],
                    ],
                    'title' => __('Use captcha', 'rd-downloads'),
                    'type' => 'checkbox',
                ],
                [
                    'options' => [
                        [
                            'default' => '',
                            /* translators: %s: "Use captcha" form name text. */
                            'description' => sprintf(
                                __('If %s was checked, this checkbox is for allow play captcha audio. If your server does not support or not working then uncheck this box.', 'rd-downloads'),
                                '<strong>' . __('Use captcha', 'rd-downloads') . '</strong>'
                            ) . ' ' . __('Default is no.', 'rd-downloads'),
                            'id' => 'rdd_use_captcha_audio',
                            'title' => __('Yes', 'rd-downloads'),
                            'value' => '1',
                        ],
                    ],
                    'title' => __('Use captcha audio', 'rd-downloads'),
                    'type' => 'checkbox',
                ],
                [
                    'default' => "bot\nyahoo! slurp",
                    'description' => __('User agent that contain text in one of this will be blocked. One per line, case insensitive.', 'rd-downloads'),
                    'id' => 'rdd_block_ua',
                    'input_attributes' => [
                        'rows' => 5,
                    ],
                    'title' => __('Block user agents', 'rd-downloads'),
                    'type' => 'textarea',
                ],
            ],// fields
        ],// end general tab.
        [
            'icon' => 'fab fa-github',
            'title' => __('GitHub', 'rd-downloads'),
            'fields' => [
                [
                    'default' => '',
                    'description' => '<button id="rd-downloads-settings-regenerate-secret" class="button" type="button"><i class="re-generate-github-secret-icon fas fa-sync-alt"></i> ' . __('Re-generate', 'rd-downloads') . '</button><br>' .
                        __('Secret key for use in GitHub webhook auto update', 'rd-downloads'),
                    'id' => 'rdd_github_secret',
                    'input_attributes' => [
                        'readonly' => '',
                    ],
                    'title' => __('GitHub Secret', 'rd-downloads'),
                    'type' => 'text',
                ],
                [
                    'default' => 'release',
                    'description' => __('Choose how auto update work. Auto update on every releases and commits can make your server slow, if you choose every releases then it will work less.', 'rd-downloads'),
                    'id' => 'rdd_github_auto_update',
                    'options' => [
                        '' => __('Do not auto update', 'rd-downloads'),
                        'release' => __('Every releases', 'rd-downloads') . ' (' . __('Default', 'rd-downloads') . ')',
                        'release+commit' => __('Every releases and commits', 'rd-downloads'),
                    ],
                    'title' => __('Auto update', 'rd-downloads'),
                    'type' => 'select',
                ],
                [
                    'content' => $githubSecretHelp,
                    'title' => '',
                    'type' => 'html',
                ],
                [
                    'default' => '',
                    'description' => '<button id="rd-downloads-settings-test-token" class="button" type="button">' . __('Test token', 'rd-downloads') . '</button><br>' . 
                        __('Token that can be used to access GitHub API such as auto correct repository URL.', 'rd-downloads') . ' ' .
                        __('This is required to use with GitHub auto update.', 'rd-downloads'),
                    'id' => 'rdd_github_token',
                    'title' => __('GitHub token', 'rd-downloads'),
                    'type' => 'text',
                ],
                [
                    'content' => $githubTokenHelp,
                    'title' => '',
                    'type' => 'html',
                ],
            ],// fields
        ],// end GitHub tab.
        [
            'icon' => 'fas fa-chart-bar',
            'title' => __('Logs/statistic', 'rd-downloads'),
            'fields' => [
                [
                    'options' => [
                        [
                            'default' => '1',
                            'description' => __('Check this to automatically delete old logs.', 'rd-downloads') . ' ' . __('Default is yes.', 'rd-downloads'),
                            'id' => 'rdd_auto_delete_logs',
                            'title' => __('Yes', 'rd-downloads'),
                            'value' => '1',
                        ],
                    ],
                    'title' => __('Auto delete logs', 'rd-downloads'),
                    'type' => 'checkbox',
                ],
                [
                    'default' => '90',
                    /* translators: %s is Auto delete logs option */
                    'description' => sprintf(__('Auto delete logs after specific days. This will be skipped if %s was not checked.', 'rd-downloads'), '<strong>' . __('Auto delete logs', 'rd-downloads') . '</strong>') . ' ' . __('Default is 90.', 'rd-downloads'),
                    'id' => 'rdd_auto_delete_logs_days',
                    'input_attributes' => [
                        'max' => 365,
                        'min' => 1,
                        'step' => 1,
                    ],
                    'title' => __('Days limit', 'rd-downloads'),
                    'type' => 'number',
                ],
                [
                    'options' => [
                        [
                            'default' => '1',
                            'description' => __('Check this to logs admin actions such as add, update, delete downloads.', 'rd-downloads') . ' ' . __('Default is yes.', 'rd-downloads'),
                            'id' => 'rdd_admin_logs',
                            'title' => __('Yes', 'rd-downloads'),
                            'value' => '1',
                        ],
                    ],
                    'title' => __('Admin actions', 'rd-downloads'),
                    'type' => 'checkbox',
                ],
            ],// fields
        ],// end logs/stat tab.
        [
            'icon' => 'fas fa-paint-brush',
            'title' => __('Design', 'rd-downloads'),
            'fields' => [
                [
                    'default' => $designDefaultValue,
                    'id' => 'rdd_download_element',
                    // mode refer from https://github.com/ajaxorg/ace/blob/master/lib/ace/ext/modelist.js#L53
                    'mode' => 'html',
                    'title' => __('Downloads element', 'rd-downloads'),
                    'type' => 'code_editor',
                ],
                [
                    'content' => $designHelp,
                    'title' => '',
                    'type' => 'html',
                ],
            ],// fields
        ],// end customize element tab.
    ],// setting_tabs
];
<?php
/**
 * RundizSettings configuration file.
 * Name this file freedomly, but include it correctly in \App\config\config.php
 * 
 * Restricted field id: rdsfw_plugin_db_version, rdsfw_manual_update_version.
 * 
 * @package rundiz-downloads
 */


/*return [
    // tab_style is vertical or horizontal
    'tab_style' => 'vertical',
    'setting_tabs' => [
        [
            'icon' => 'fas fa-pencil-alt',
            'title' => __('Basic fields', 'rundiz-downloads'),
            'fields' => [
                [
                    'default' => '',
                    'description' => __('Form field description', 'rundiz-downloads'),
                    'id' => 'input_text',
                    'title' => __('Input type text', 'rundiz-downloads'),
                    'type' => 'text',
                ],
                [
                    'default' => '',
                    'id' => 'input_password',
                    'title' => __('Input type password', 'rundiz-downloads'),
                    'type' => 'password',
                ],
                [
                    'default' => '#79c1fd',
                    'id' => 'input_color',
                    'input_attributes' => [
                        'class' => 'small-text',
                    ],
                    'title' => __('Input type color', 'rundiz-downloads'),
                    'type' => 'color',
                ],
                [
                    'default' => date('Y-m-d'),
                    'id' => 'input_date',
                    'title' => __('Input type date', 'rundiz-downloads'),
                    'type' => 'date',
                ],
                [
                    'default' => '',
                    'id' => 'input_email',
                    'title' => __('Input type email', 'rundiz-downloads'),
                    'type' => 'email',
                ],
                [
                    'default' => '',
                    'id' => 'input_number',
                    'input_attributes' => [
                        'max' => 255,
                        'min' => 1,
                        'step' => 1,
                    ],
                    'title' => __('Input type number', 'rundiz-downloads'),
                    'type' => 'number',
                ],
                [
                    'default' => '0',
                    'id' => 'input_range',
                    'input_attributes' => [
                        'max' => 10,
                        'min' => 0,
                    ],
                    'title' => __('Input type range', 'rundiz-downloads'),
                    'type' => 'range',
                ],
                [
                    'default' => '',
                    'id' => 'input_url',
                    'title' => __('Input type URL', 'rundiz-downloads'),
                    'type' => 'url',
                ],
                [
                    'default' => '',
                    'id' => 'input_textarea',
                    'input_attributes' => [
                        'rows' => 5,
                    ],
                    'title' => __('Textarea', 'rundiz-downloads'),
                    'type' => 'textarea',
                ],
            ],
        ],// end basic fields
        [
            'icon' => 'far fa-check-square',
            'title' => __('Check boxes and radio buttons', 'rundiz-downloads'),
            'fields' => [
                [
                    'options' => [
                        [
                            'default' => '1',
                            'id' => 'checkbox1',
                            'title' => __('Check box 1', 'rundiz-downloads'),
                            'value' => '1',
                        ],
                        [
                            'default' => '',
                            'description' => __('This check box have extra attributes', 'rundiz-downloads'),
                            'id' => 'checkbox2',
                            'input_attributes' => ['data-test' => 'true', 'data-attribute2' => 'special-value'],
                            'title' => __('Check box 2', 'rundiz-downloads'),
                            'value' => '1',
                        ],
                    ],
                    'title' => __('Check box', 'rundiz-downloads'),
                    'type' => 'checkbox',
                ],
                [
                    'default' => ['2', '3'],
                    'options' => [
                        [
                            'id' => 'checkbox_arr[]',
                            'title' => __('Check box array 1', 'rundiz-downloads'),
                            'value' => '1',
                        ],
                        [
                            'id' => 'checkbox_arr[]',
                            'title' => __('Check box array 2', 'rundiz-downloads'),
                            'value' => '2',
                        ],
                        [
                            'id' => 'checkbox_arr[]',
                            'title' => __('Check box array 3', 'rundiz-downloads'),
                            'value' => '3',
                        ],
                    ],
                    'title' => __('Check box array', 'rundiz-downloads'),
                    'type' => 'checkbox',
                ],
                [
                    'default' => '3',
                    'id' => 'input_radio',
                    'options' => [
                        [
                            'title' => __('Radio option 1', 'rundiz-downloads'),
                            'value' => '1',
                            'input_attributes' => ['data-extra-attribute' => 'yes', 'data-input-type' => 'radio'],
                            'description' => __('This radio have extra attributes', 'rundiz-downloads'),
                        ],
                        [
                            'title' => __('Radio option 2', 'rundiz-downloads'),
                            'value' => '2',
                        ],
                        [
                            'title' => __('Radio option 3', 'rundiz-downloads'),
                            'value' => '3',
                        ],
                    ],
                    'title' => __('Input radio', 'rundiz-downloads'),
                    'type' => 'radio',
                ],
            ],
        ],// end check boxes and radio buttons
        [
            'icon' => 'fas fa-list-alt',
            'title' => __('Select boxes', 'rundiz-downloads'),
            'fields' => [
                [
                    'default' => 'AA',
                    'id' => 'select_box',
                    'options' => [
                        '' => '',
                        'A' => __('Option A', 'rundiz-downloads'),
                        'B' => __('Option B', 'rundiz-downloads'),
                        'C' => __('Option C', 'rundiz-downloads'),
                        'AA' => __('Option AA', 'rundiz-downloads'),
                    ],
                    'title' => __('Select box', 'rundiz-downloads'),
                    'type' => 'select',
                ],
                [
                    'default' => 'THA',
                    'id' => 'select_optgroup',
                    'options' => [
                        __('America', 'rundiz-downloads') => [
                            'CAN' => __('Canada', 'rundiz-downloads'),
                            'USA' => __('America', 'rundiz-downloads'),
                        ],
                        __('Asia', 'rundiz-downloads') => [
                            'CHN' => __('China', 'rundiz-downloads'),
                            'THA' => __('Thailand', 'rundiz-downloads'),
                        ],
                        __('Europe', 'rundiz-downloads') => [
                            'FRA' => __('France', 'rundiz-downloads'),
                            'GER' => __('Germany', 'rundiz-downloads'),
                        ],
                    ],
                    'title' => __('Select box with optgroup', 'rundiz-downloads'),
                    'type' => 'select',
                ],
                [
                    'default' => ['A', 'AA'],
                    'id' => 'select_multiple[]',
                    'input_attributes' => [
                        'multiple' => '',
                    ],
                    'options' => [
                        'A' => __('Option A', 'rundiz-downloads'),
                        'B' => __('Option B', 'rundiz-downloads'),
                        'C' => __('Option C', 'rundiz-downloads'),
                        'AA' => __('Option AA', 'rundiz-downloads'),
                        'AB' => __('Option AB', 'rundiz-downloads'),
                        'AC' => __('Option AC', 'rundiz-downloads'),
                    ],
                    'title' => __('Select box multiple', 'rundiz-downloads'),
                    'type' => 'select',
                ],
                [
                    'default' => ['THA', 'CAN'],
                    'id' => 'select_multiple2[]',
                    'input_attributes' => [
                        'multiple' => '',
                    ],
                    'options' => [
                        __('America', 'rundiz-downloads') => [
                            'CAN' => __('Canada', 'rundiz-downloads'),
                            'USA' => __('America', 'rundiz-downloads'),
                        ],
                        __('Asia', 'rundiz-downloads') => [
                            'CHN' => __('China', 'rundiz-downloads'),
                            'THA' => __('Thailand', 'rundiz-downloads'),
                        ],
                        __('Europe', 'rundiz-downloads') => [
                            'FRA' => __('France', 'rundiz-downloads'),
                            'GER' => __('Germany', 'rundiz-downloads'),
                        ],
                    ],
                    'title' => __('Select box multiple with optgroup', 'rundiz-downloads'),
                    'type' => 'select',
                ],
            ],
        ],// end select boxes
        [
            'icon' => 'fas fa-edit',
            'title' => __('Editors', 'rundiz-downloads'),
            'fields' => [
                [
                    'default' => 'Rundiz Settings',
                    'description' => __('Use WordPress editor to edit text/html', 'rundiz-downloads'),
                    'editor_settings' => [
                        // editor settings refer from https://codex.wordpress.org/Function_Reference/wp_editor
                        'textarea_rows' => 5,
                    ],
                    'id' => 'field_editor',
                    'title' => __('Editor', 'rundiz-downloads'),
                    'type' => 'editor',
                ],
                [
                    'default' => 'Rundiz Settings',
                    'editor_settings' => [
                        // editor settings refer from https://codex.wordpress.org/Function_Reference/wp_editor
                        'media_buttons' => false,
                        'teeny' => true,
                        'textarea_rows' => 5,
                    ],
                    'id' => 'field_editor_tiny_no_media',
                    'title' => __('Editor mini without media button', 'rundiz-downloads'),
                    'type' => 'editor',
                ],
                [
                    'default' => 'Rundiz Settings',
                    'editor_settings' => [
                        // editor settings refer from https://codex.wordpress.org/Function_Reference/wp_editor
                        'teeny' => true,
                        'textarea_rows' => 5,
                    ],
                    'id' => 'field_editor_tiny_media',
                    'title' => __('Editor mini with media button', 'rundiz-downloads'),
                    'type' => 'editor_full',
                ],
            ],
        ],// end editor fields
        [
            'icon' => 'fas fa-code',
            'title' => __('Code editors', 'rundiz-downloads'),
            'fields' => [
                [
                    'default' => '<!DOCTYPE html>'."\n".'<html>'."\n\t".'<head>'."\n\t\t".'<meta charset="utf-8">'."\n\t\t".'<title>HTML Title</title>'."\n\t".'</head>'."\n\t".'<body>'."\n\t\t".'<p>HTML Text in body.</p>'."\n\t".'</body>'."\n".'</html>',
                    'description' => __('Use Ace editor to edit source code', 'rundiz-downloads'),
                    'id' => 'code_editor_html',
                    // mode refer from https://github.com/ajaxorg/ace/blob/master/lib/ace/ext/modelist.js#L53
                    'mode' => 'html',
                    'title' => __('Code editor (HTML)', 'rundiz-downloads'),
                    'type' => 'code_editor',
                ],
                [
                    'default' => 'function returnSomeWord(string) {'."\n\t".'return "This is some word in return: "+string;'."\n".'}',
                    'id' => 'code_editor_js',
                    // mode refer from https://github.com/ajaxorg/ace/blob/master/lib/ace/ext/modelist.js#L53
                    'mode' => 'javascript',
                    'title' => __('Code editor (JS)', 'rundiz-downloads'),
                    'type' => 'code_editor',
                ],
                [
                    'default' => '.my-css-class {'."\n\t".'background: #fff;'."\n\t".'color: #333;'."\n".'}',
                    'id' => 'code_editor_css',
                    // mode refer from https://github.com/ajaxorg/ace/blob/master/lib/ace/ext/modelist.js#L53
                    'mode' => 'css',
                    'title' => __('Code editor (CSS)', 'rundiz-downloads'),
                    'type' => 'code_editor',
                ],
            ],
        ],// end code editors
        [
            'icon' => 'far fa-image',
            'title' => __('Media fields', 'rundiz-downloads'),
            'fields' => [
                [
                    'default' => '',
                    'description' => __('Media upload button with full preview.', 'rundiz-downloads'),
                    'id' => 'mediaupload',
                    'title' => __('Media', 'rundiz-downloads'),
                    'type' => 'media',
                ],
                [
                    'default' => '',
                    'id' => 'mediaupload_no_preview_img',
                    // mode for media are: preview_all, preview_url, preview_img, no_preview_img, no_preview_url. choose one.
                    'mode' => 'no_preview_img',
                    'title' => __('Media no preview image', 'rundiz-downloads'),
                    'type' => 'media',
                ],
                [
                    'default' => '',
                    'id' => 'mediaupload_no_preview_url',
                    // mode for media are: preview_all, preview_url, preview_img, no_preview_img, no_preview_url. choose one.
                    'mode' => 'no_preview_url',
                    'title' => __('Media no preview URL', 'rundiz-downloads'),
                    'type' => 'media',
                ],
            ],
        ],// end media fields
        [
            'icon' => 'fas fa-tv',
            'title' => __('Presentation fields', 'rundiz-downloads'),
            'fields' => [
                [
                    'content' => __('Presentation field in normal <strong>2 columns</strong> for medium display or larger.', 'rundiz-downloads'),
                    'title' => __('Presentation in 2 columns', 'rundiz-downloads'),
                    'type' => 'html',
                ],
                [
                    'content' => __('Presentation field in <strong>full column</strong> display. You can use any html element in <code>html</code> and <code>html_full</code> field type', 'rundiz-downloads'),
                    'type' => 'html_full',
                ],
            ],
        ],// end presentation fields
    ],
];

*/
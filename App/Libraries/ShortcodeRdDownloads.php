<?php
/**
 * Shortcode class for [rddownloads]
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\ShortcodeRdDownloads')) {
    class ShortcodeRdDownloads
    {


        use \RdDownloads\App\AppTrait;


        /**
         * Get all available attributes.
         * 
         * @return array
         */
        public function availableAttributes()
        {
            return [
                'id' => [
                    'default' => '',
                    'helpmsg' => __('Required attribute for display link to download.', 'rd-downloads'),
                ],// REQUIRED. based attribute that will convert to html with link.
                'display_size' => [
                    'default' => '',
                    'isbool' => true,
                    /* translators: %s: true (boolean) */
                    'helpmsg' => sprintf(__('Set to %s to display file size. Default is not set.', 'rd-downloads'), '<code>true</code>'),
                ],// for toggle display file size or not. its value is boolean.
                'display_file_name' => [
                    'default' => '',
                    'isbool' => true,
                    /* translators: %s: true (boolean) */
                    'helpmsg' => sprintf(__('Set to %s to display file name. Default is not set.', 'rd-downloads'), '<code>true</code>'),
                ],// for toggle display file name or not. its value is boolean.
                'display_download_count' => [
                    'default' => '',
                    'isbool' => true,
                    /* translators: %s: true (boolean) */
                    'helpmsg' => sprintf(__('Set to %s to display download count. Default is not set.', 'rd-downloads'), '<code>true</code>'),
                ],// for toggle display download count or not. its value is boolean.
                'display_download_version' => [
                    'default' => '',
                    'isbool' => true,
                    /* translators: %s: true (boolean) */
                    'helpmsg' => sprintf(__('Set to %s to display download file version. Default is not set.', 'rd-downloads'), '<code>true</code>'),
                ],
                'display_create_date' => [
                    'default' => '',
                    'isbool' => true,
                    /* translators: %s: true (boolean) */
                    'helpmsg' => sprintf(__('Set to %s to display create date. Default is not set.', 'rd-downloads'), '<code>true</code>'),
                ],// for toggle display create date or not. its value is boolean.
                'display_last_update' => [
                    'default' => '',
                    'isbool' => true,
                    /* translators: %s: true (boolean) */
                    'helpmsg' => sprintf(__('Set to %s to display last update. Default is not set.', 'rd-downloads'), '<code>true</code>'),
                ],// for toggle display last update or not. its value is boolean.
                'datetime_format' => [
                    'default' => '',
                    /* translators: %1$s: Open link, %2$s: Close link, %3$s: Default value. */
                    'helpmsg' => sprintf(__('Use %1$sPHP date/time format%2$s. Default is %3$s.', 'rd-downloads'), '<a href="http://php.net/manual/en/datetime.format.php" target="php_datetime_format">', '</a>', '<code>' . get_option('date_format') . ' ' . get_option('time_format') . '</code>'),
                ],// date/time format same as `date()` in PHP. its value is string.
            ];
        }// availableAttributes


        /**
         * Combine attributes from user with default.
         * 
         * It will be remove attribute that must be boolean but its value is not 'true'.<br>
         * Also remove un-necessary attribute such as empty attribute value.
         * 
         * @param array $userAttributes The user input shortcode attributes.
         * @return array Return modified attributes.
         */
        protected function combineAttributes(array $userAttributes)
        {
            $defaultAttributes = [];
            foreach ($this->availableAttributes() as $attribute_name => $item) {
                $defaultAttributes[$attribute_name] = (isset($item['default']) ? $item['default'] : '');
            }// endforeach;
            unset($attribute_name, $item);

            $combinedAttributes = shortcode_atts($defaultAttributes, $userAttributes);
            unset($defaultAttributes);

            // loop combined attributes to remove unnecessary items.
            if (is_array($combinedAttributes)) {
                $availableAttributes = $this->availableAttributes();
                foreach ($combinedAttributes as $attribute_name => $value) {
                    if (
                        isset($availableAttributes[$attribute_name]['isbool']) && 
                        $availableAttributes[$attribute_name]['isbool'] === true
                    ) {
                        // if default available attributes of this name is exists and its value must be boolean.
                        if (strtolower($value) !== 'true') {
                            // if the combined attribute's value is not true.
                            unset($combinedAttributes[$attribute_name]);
                        }
                    }
                    if (
                        strtolower($attribute_name) !== 'id' &&
                        $value == ''
                    ) {
                        // if attribute name is not id and its value is empty.
                        unset($combinedAttributes[$attribute_name]);
                    }
                }// endforeach;
                unset($attribute_name, $availableAttributes, $value);
            }

            return $combinedAttributes;
        }// combineAttributes


        /**
         * Render download HTML element from shortcode.
         * 
         * @global array $rd_downloads_options
         * @param array $userAttributes The user input shortcode attributes.
         * @return string Return rendered HTML.
         */
        public function renderHtml($userAttributes)
        {
            $combinedAttributes = $this->combineAttributes($userAttributes);

            if (!isset($combinedAttributes['id'])) {
                // if not found attribute "id" in shortcode.
                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    return '<!-- not found attribute id -->';
                } else {
                    unset($combinedAttributes);
                    return '';
                }
            }

            // get data from DB.
            $RdDownloads = new \RdDownloads\App\Models\RdDownloads();
            $DlRow = $RdDownloads->get(['download_id' => $combinedAttributes['id']]);
            unset($RdDownloads);

            if (empty($DlRow)) {
                // if download_id was not found in DB.
                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    return '<!-- not found download_id ' . $combinedAttributes['id'] . ' -->';
                } else {
                    unset($combinedAttributes);
                    return '';
                }
            }

            $this->getOptions();
            global $rd_downloads_options;

            $ElementPlaceholders = new ElementPlaceholders();
            // set HTML template from setting page.
            if (
                isset($rd_downloads_options['rdd_download_element']) && 
                is_scalar($rd_downloads_options['rdd_download_element']) && 
                !empty(trim($rd_downloads_options['rdd_download_element']))
            ) {
                $templateString = $rd_downloads_options['rdd_download_element'];
            } else {
                $templateString = $ElementPlaceholders->defaultDownloadHtml();
            }

            // extract options for placeholder.
            $download_options = maybe_unserialize($DlRow->download_options);

            // set template data -----------------------------------------------------------------------
            $templateData = $ElementPlaceholders->textPlaceholders();
            foreach ($ElementPlaceholders->dbPlaceholders() as $db_placeholder) {
                if (isset($DlRow->{$db_placeholder})) {
                    $templateData[$db_placeholder] = $DlRow->{$db_placeholder};
                } elseif (isset($download_options[$db_placeholder])) {
                    $templateData[$db_placeholder] = $download_options[$db_placeholder];
                } else {
                    $templateData[$db_placeholder] = '';
                }
            }// endforeachl
            unset($db_placeholder);
            $templateData = array_merge($templateData, $combinedAttributes);

            unset($download_options);

            // make sure that these data will not exposed or remove if it is just empty.
            $Url = new Url();
            $templateData['download_url'] = $Url->getDownloadPageUrl($combinedAttributes['id']);
            unset($Url);
            if (empty($templateData['download_github_name'])) {
                unset($templateData['download_github_name']);
            }
            // make sure download file size will be format to readable.
            $templateData['download_size'] = str_replace('.00', '', size_format(intval($templateData['download_size']), 2));
            // make sure dates will be format -------------------------
            if (isset($combinedAttributes['datetime_format'])) {
                $datetimeFormat = $combinedAttributes['datetime_format'];
            } else {
                // date/time format was not set, use default.
                $datetimeFormat = get_option('date_format') . ' ' . get_option('time_format');
            }
            $dateCreateFromGmt = get_date_from_gmt($templateData['download_create'], 'U');
            $templateData['download_create_gmt'] = date_i18n($datetimeFormat . (strpos($datetimeFormat, 'P') === false ? ' P' : ''), $dateCreateFromGmt);
            $dateUpdateFromGmt = get_date_from_gmt($templateData['download_update'], 'U');
            $templateData['download_update_gmt'] = date_i18n($datetimeFormat . (strpos($datetimeFormat, 'P') === false ? ' P' : ''), $dateUpdateFromGmt);
            $templateData['download_create'] = date_i18n($datetimeFormat, strtotime($templateData['download_create']));
            $templateData['download_update'] = date_i18n($datetimeFormat, strtotime($templateData['download_update']));
            unset($dateCreateFromGmt, $datetimeFormat, $dateUpdateFromGmt);
            // end dates format -----------------------------------------
            unset($combinedAttributes);
            // end set template data -------------------------------------------------------------------

            unset($ElementPlaceholders);

            $Template = new Template();
            $Template->setTemplate($templateString, $templateData);
            unset($DlRow, $templateString, $templateData);
            return $Template->get();
        }// renderHtml


    }
}
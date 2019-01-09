<?php
/**
 * Rundiz Downloads list table.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Models;

if (!class_exists('\\RdDownloads\\App\\Models\\RdDownloadsListTable')) {
    /**
     * List data into table.
     * Warning! Do not modify method name because they are extended from WP_List_Table class of WordPress. Changing the method name may cause program error.
     * Warning! this parent class is marked as private. Please read at wordpress source.
     *
     * @link http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/ tutorial about how to list table data.
     * @link http://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/ another tutorial
     * @link https://codex.wordpress.org/Class_Reference/WP_List_Table wordpress list table class source.
     */
    class RdDownloadsListTable extends WPListTable
    {


        /**
         * Check that is this user have enough permission to edit.
         *
         * Current user id match db then require just `upload_files` permission.<br>
         * Current user id NOT match db then require `edit_others_posts` permission.
         *
         * @param object $item
         * @return boolean
         */
        private function checkPermissionEdit($item)
        {
            if (!is_array($item) && !is_object($item)) {
                return false;
            }

            return ($item->user_id == get_current_user_id() && current_user_can('upload_files')) || ($item->user_id != get_current_user_id() && current_user_can('edit_others_posts'));
        }// checkPermissionEdit


        /**
         * {@inheritDoc}
         */
        protected function column_cb($item)
        {
            if (!$this->checkPermissionEdit($item)) {
                return '';
            }

            return sprintf('<input type="checkbox" name="download_id[]" value="%d">', intval($item->download_id));
        }// column_checkbox


        /**
         * {@inheritDoc}
         */
        protected function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'user_id':
                    $output = '';
                    if (current_user_can('edit_users')) {
                        $output .= '<a href="' . esc_url(get_edit_user_link($item->{$column_name})) . '">';
                    }
                    $output .= $item->display_name;
                    if (current_user_can('edit_users')) {
                        $output .= '</a>';
                    }
                    return $output;
                case 'download_file_name':
                    return '<span title="' . esc_attr($item->{$column_name}) . '">' . esc_html($item->{$column_name}) . '</span>';
                case 'download_size':
                    $size = ($item->{$column_name} != null ? intval($item->{$column_name}) : 0);
                    return str_replace('.00', '', size_format($size, 2));
                case 'shortcode':
                    return '<input class="shortcode-text" type="text" readonly="readonly" value="[rddownloads id=&quot;' . esc_attr($item->download_id) . '&quot;]">' .
                        '<div class="copied-msg hidden"><i>' . __('Shortcode copied to clipboard.', 'rd-downloads') . '</i></div>';
                case 'opt_force_download':
                    $download_options = maybe_unserialize($item->download_options);
                    if (isset($download_options['opt_force_download'])) {
                        switch ($download_options['opt_force_download']) {
                            case '0':
                                return '<i class="fas fa-times" title="' . esc_attr__('No', 'rd-downloads') . '"></i><span class="sr-only">' . __('No', 'rd-downloads') . '</span>';
                            case '1':
                                return '<i class="fas fa-check" title="' . esc_attr__('Yes', 'rd-downloads') . '"></i><span class="sr-only">' . __('Yes', 'rd-downloads') . '</span>';
                            default:
                                return '<i class="fas fa-question" title="' . esc_attr__('Default', 'rd-downloads') . '"></i><span class="sr-only">' . __('Default', 'rd-downloads') . '</span>';
                        }// endswitch;
                    }// endif;
                case 'download_count':
                    $output = '<span title="' . esc_attr(number_format($item->{$column_name})) . '">';
                    $number = intval($item->{$column_name});
                    if ($number > 1000 && $number < 10000) {
                        $output .= '&gt;' . number_format(preg_replace('#(\d)(\d{3})#', '${1}000', $number));
                    } elseif ($number > 10000 && $number < 100000) {
                        $output .= '&gt;' . number_format(preg_replace('#(\d)(\d{4})#', '${1}0000', $number));
                    } elseif ($number > 100000 && $number < 1000000) {
                        $output .= '&gt;' . number_format(preg_replace('#(\d)(\d{5})#', '${1}00000', $number));
                    } elseif ($number > 1000000) {
                        $output .= '&gt;' . number_format(1000000);
                    } else {
                        $output .= number_format($number);
                    }
                    unset($number);
                    $output .= '</span>';
                    return $output;
                case 'download_create':
                case 'download_create_gmt':
                case 'download_update':
                case 'download_update_gmt':
                    $output = '';
                    $datetime_local = strtotime(get_date_from_gmt($item->download_create_gmt, 'Y-m-d H:i:s'));
                    $datetime_withtimezone = date_i18n('Y-m-d H:i:s (P)', $datetime_local);
                    /* translators: %s: Date/time value. */
                    $output .= sprintf(__('Created: %s', 'rd-downloads'), '<time datetime="' . esc_attr(mysql2date('c', $item->download_create_gmt, false)) . '" title="' . esc_attr($datetime_withtimezone) . '">' . date('Y-m-d', strtotime($item->download_create)) . '</time>') . PHP_EOL;
                    $output .= '<br>' . PHP_EOL;
                    $datetime_local = strtotime(get_date_from_gmt($item->download_update_gmt, 'Y-m-d H:i:s'));
                    $datetime_withtimezone = date_i18n('Y-m-d H:i:s (P)', $datetime_local);
                    /* translators: %s: Date/time value. */
                    $output .= sprintf(__('Updated: %s', 'rd-downloads'), '<time datetime="' . esc_attr(mysql2date('c', $item->download_update_gmt, false)) . '" title="' . esc_attr($datetime_withtimezone) . '">' . date('Y-m-d', strtotime($item->download_update)) . '</time>') . PHP_EOL;
                    unset($datetime_local, $datetime_withtimezone);
                    return $output;
                default:
                    if (isset($item->{$column_name}) && is_scalar($item->{$column_name})) {
                        return esc_html($item->{$column_name});
                    }
                    return '';
            }// endswitch;
        }// column_default


        /**
         * Column download_name.
         *
         * @param object $item
         * @return string
         */
        protected function column_download_name($item)
        {
            $output = '<strong>';

            if ($this->checkPermissionEdit($item)) {
                $output .= '<a class="row-title" href="' . esc_url(admin_url('admin.php?page=rd-downloads_edit&amp;download_id=' . $item->download_id)) . '" title="' . esc_attr($item->download_name) . '">';
            }

            $output .= esc_html(mb_strimwidth($item->download_name, 0, 47, '...'));

            if ($this->checkPermissionEdit($item)) {
                $output .= '</a>';
            }

            $output .= '</strong>';

            if (isset($item->download_options)) {
                $download_options = maybe_unserialize($item->download_options);
                if (
                    is_array($download_options) &&
                    array_key_exists('opt_download_version', $download_options) &&
                    !empty($download_options['opt_download_version'])
                ) {
                    /* translators: %s: version number. */
                    $output .= ' <i class="rd-downloads-version" title="' . sprintf(esc_attr__('Version %s', 'rd-downloads'), $download_options['opt_download_version']) . '">(' . esc_html($download_options['opt_download_version']) . ')</i>';
                }
                unset($download_options);
            }

            return $output;
        }// column_download_type


        /**
         * Column download_type.
         *
         * @param object $item
         * @return string
         */
        protected function column_download_type($item)
        {
            switch ($item->download_type) {
                case '0':
                    return '<i class="fas fa-box download_type_icon" title="' . esc_attr__('Local file', 'rd-downloads') . '"></i> <span class="download_type_text">' . __('Local file', 'rd-downloads') . '</span>';
                case '1':
                    return '<i class="fab fa-github download_type_icon" title="' . esc_attr__('GitHub file', 'rd-downloads') . '"></i> <span class="download_type_text">' . __('GitHub file', 'rd-downloads') . '</span>';
                case '2':
                    return '<i class="fas fa-globe download_type_icon" title="' . esc_attr__('Any remote file', 'rd-downloads') . '"></i> <span class="download_type_text">' . __('Any remote file', 'rd-downloads') . '</span>';
                default:
                    return '<i class="fas fa-question download_type_icon" title="' . esc_attr__('Unknown', 'rd-downloads') . '"></i> <span class="download_type_text">' . __('Unknown', 'rd-downloads') . '</span>';
            }
        }// column_download_type


        /**
         * Get bulk actions
         *
         * @return array return array key and name. example: [key1 => describe how this work]
         */
        protected function get_bulk_actions()
        {
            if (!current_user_can('upload_files')) {
                return [];
            }

            $actions = [];

            $Github = new \RdDownloads\App\Libraries\Github();
            $accessToken = $Github->getOAuthAccessToken();
            unset($Github);

            if ($accessToken !== false) {
                $actions['githubUpdate'] = __('Update GitHub', 'rd-downloads');
            }
            unset($accessToken);
            $actions['remoteUpdate'] = __('Update remote file', 'rd-downloads');
            $actions['delete'] = __('Delete', 'rd-downloads');

            return $actions;
        }// get_bulk_actions


        /**
         * {@inheritDoc}
         */
        public function get_columns()
        {
            $columns = [
                'cb' => '<input type="checkbox">',
                'download_name' => __('Downloads name', 'rd-downloads'),
                'user_id' => __('Author', 'rd-downloads'),
                'download_type' => '<i class="fas fa-server download_type_icon" title="' . esc_attr__('Type', 'rd-downloads') . '"></i> <span class="download_type_text">' . __('Type', 'rd-downloads') . '</span>',
                'download_file_name' => __('File', 'rd-downloads'),
                'download_size' => __('Size', 'rd-downloads'),
                'shortcode' => __('Shortcode', 'rd-downloads'),
                'opt_force_download' => '<span title = "' . esc_attr__('Force download', 'rd-downloads') . '"><i class="fas fa-arrow-alt-circle-down"></i> <span class="sr-only">' . __('Force download', 'rd-downloads') . '</span></span>',
                'download_count' => '<span title = "' . esc_attr__('Download count', 'rd-downloads') . '"><i class="fas fa-chart-line"></i> <span class="sr-only">' . __('Download count', 'rd-downloads') . '</span></span>',
                'download_create_gmt' => '<span title="' . esc_attr__('Date', 'rd-downloads') . '"><i class="far fa-calendar-alt"></i> <span class="sr-only">' . __('Date', 'rd-downloads') . '</span></span>',
            ];

            return $columns;
        }// get_columns


        /**
         * {@inheritDoc}
         */
        protected function get_sortable_columns()
        {
            $sortable_columns = [
                'download_id' => ['download_id', false],
                'user_id' => ['user_id', false],
                'download_name' => ['download_name', false],
                'download_file_name' => ['download_file_name', false],
                'download_size' => ['download_size', false],
                'download_count' => ['download_count', false],
                'download_create_gmt' => ['download_create_gmt', false],
            ];

            return $sortable_columns;
        }// get_sortable_columns


        /**
         * {@inheritDoc}
         *
         * @global \wpdb $wpdb
         */
        protected function get_views()
        {
            global $wpdb;

            $views = [];
            $options = [];

            $filter_user_id = (isset($_REQUEST['filter_user_id']) && trim($_REQUEST['filter_user_id']) != null ? trim($_REQUEST['filter_user_id']) : null);
            $filter_download_type = (isset($_REQUEST['filter_download_type']) && trim($_REQUEST['filter_download_type']) != null ? trim($_REQUEST['filter_download_type']) : null);
            $search = (isset($_REQUEST['s']) && !empty(trim($_REQUEST['s'])) ? trim($_REQUEST['s']) : null);

            // get result using `RdDownloads` class.
            $RdDownloads = new RdDownloads();
            $options['*select'] = 'COUNT(download_id)';
            $options['*return_prepare'] = true;
            if (!empty($search)) {
                $options['*search'] = $search;
            }
            $sql = $RdDownloads->get($options);
            unset($search);

            // all items
            $class = ($filter_download_type == null && $filter_user_id == null ? ' class="current"' : '');
            $views['all'] = '<a' . $class . ' href="' . esc_url(remove_query_arg(['filter_download_type', 'filter_user_id'])) . '">' . __('All', 'rd-downloads') . ' <span class="count">(' . $wpdb->get_var($sql) . ')</span></a>';
            unset($class);

            // local uploaded items
            $options['download_type'] = '0';
            $sqlFiltered = $RdDownloads->get($options);
            $class = ($filter_download_type == $options['download_type'] ? ' class="current"' : '');
            $views['download_type_' . $options['download_type']] = '<a' . $class . ' href="' . esc_url(add_query_arg('filter_download_type', $options['download_type'])) . '">' . __('Local file', 'rd-downloads') . ' <span class="count">(' . $wpdb->get_var($sqlFiltered) . ')</span></a>';
            unset($class, $options['download_type'], $sqlFiltered);

            // GitHub items
            $options['download_type'] = '1';
            $sqlFiltered = $RdDownloads->get($options);
            $class = ($filter_download_type == $options['download_type'] ? ' class="current"' : '');
            $views['download_type_' . $options['download_type']] = '<a' . $class . ' href="' . esc_url(add_query_arg('filter_download_type', $options['download_type'])) . '">' . __('GitHub file', 'rd-downloads') . ' <span class="count">(' . $wpdb->get_var($sqlFiltered) . ')</span></a>';
            unset($class, $options['download_type'], $sqlFiltered);

            // any remote file
            $options['download_type'] = '2';
            $sqlFiltered = $RdDownloads->get($options);
            $class = ($filter_download_type == $options['download_type'] ? ' class="current"' : '');
            $views['download_type_' . $options['download_type']] = '<a' . $class . ' href="' . esc_url(add_query_arg('filter_download_type', $options['download_type'])) . '">' . __('Any remote file', 'rd-downloads') . ' <span class="count">(' . $wpdb->get_var($sqlFiltered) . ')</span></a>';
            unset($class, $options['download_type'], $sqlFiltered);

            // filtered user
            if ($filter_user_id != null) {
                $User = get_user_by('ID', $filter_user_id);
                $options['user_id'] = $filter_user_id;
                $sqlFiltered = $RdDownloads->get($options);
                /* translators: %s: Link to edit user. */
                $views['filtered_user'] = '<strong>' . sprintf(__('Filtered user: %s', 'rd-downloads'), '<a href="' . esc_url(get_edit_user_link($filter_user_id)) . '" target="editUser">' . $User->display_name . '</a>') . '</strong>';
                unset($class, $options['user_id'], $sqlFiltered, $User);

                $views['reset_filtered_user'] = '<a href="' . esc_url(remove_query_arg('filter_user_id')) . '">' . __('Reset filtered user', 'rd-downloads') . '</a>';
            }

            unset($filter_download_type, $filter_user_id, $options, $RdDownloads);
            return $views;
        }// get_views


        /**
         * {@inheritDoc}
         */
        protected function handle_row_actions($item, $column_name, $primary)
        {
            if ($column_name !== $primary) {
                return ;
            }

            $actions = [];

            if ($this->checkPermissionEdit($item)) {
                $actions['edit'] = sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(admin_url('admin.php?page=rd-downloads_edit&amp;download_id=' . $item->download_id)),
                    __('Edit')
                );
            }

            $actions['previewFile'] = sprintf(
                '<a href="%s" target="rddownloads_preview">%s</a>',
                esc_url($item->download_url),
                __('Preview', 'rd-downloads')
            );

            if ($item->download_type == '1' && !empty($item->download_github_name)) {
                $actions['githubRepository'] = sprintf(
                    '<a href="%s" target="rddownloads_github_repository">%s</a>',
                    esc_url('https://github.com/' . $item->download_github_name),
                    __('GitHub repository', 'rd-downloads')
                );
            }

            $actions['filter_user'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(add_query_arg('filter_user_id', $item->user_id)),
                __('Filter user', 'rd-downloads')
            );

            if (current_user_can('upload_files')) {
                $actions['viewLogs'] = sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(admin_url('admin.php?page=rd-downloads_logs&filter_download_id=' . $item->download_id)),
                    __('Logs', 'rd-downloads')
                );
            }

            return $this->row_actions($actions);
        }// handle_row_actions


        /**
         * prepare data and items
         *
         * @global \wpdb $wpdb
         * @param array $options available options: user_id, download_type, search, sort (column name), order (ascending descending)
         */
        public function prepare_items(array $options = [])
        {
            // prepare columns
            $columns = $this->get_columns();
            $hidden = [];
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = [$columns, $hidden, $sortable];
            unset($columns, $hidden);

            // use `RdDownloads` class to get results.
            $RdDownloads = new RdDownloads();
            // prepare options for the `listItems()` method.
            $listItemsOptions = [];
            if (isset($options['user_id'])) {
                $listItemsOptions['user_id'] = $options['user_id'];
            }
            if (isset($options['download_type'])) {
                $listItemsOptions['download_type'] = $options['download_type'];
            }
            if (isset($options['search'])) {
                $listItemsOptions['*search'] = $options['search'];
            }
            if (isset($options['sort']) && array_key_exists($options['sort'], $sortable)) {
                $listItemsOptions['*sort'] = $options['sort'];
            }
            if (isset($options['order'])) {
                $listItemsOptions['*order'] = $options['order'];
            }
            $listItemsOptions['*current_page'] = $this->get_pagenum();
            $per_page = $this->get_items_per_page('rddownloads_items_perpage');
            $listItemsOptions['*per_page'] = $per_page;
            $listItems = $RdDownloads->listItems($listItemsOptions);
            unset($listItemsOptions, $RdDownloads, $sortable);

            if (is_array($listItems) && array_key_exists('total', $listItems) && array_key_exists('results', $listItems)) {
                $total_items = $listItems['total'];
                $results = $listItems['results'];
            } else {
                $total_items = 0;
                $results = [];
            }
            unset($listItems);

            // create pagination
            $this->set_pagination_args([
                'total_items' => $total_items,
                'per_page'    => $per_page
            ]);

            $this->items = $results;
        }// prepare_items


        /**
         * {@inheritDoc}
         */
        public function single_row($item)
        {
            echo '<tr class="rd-downloads_download_id_' . $item->download_id . '">';
            $this->single_row_columns($item);
            echo '</tr>';
        }// single_row

    }
}
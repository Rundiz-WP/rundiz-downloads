<?php
/**
 * Rundiz Download Logs list table.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Models;

if (!class_exists('\\RdDownloads\\App\\Models\\RdDownloadLogsListTable')) {
    /**
     * List data into table.
     * Warning! Do not modify method name because they are extended from WP_List_Table class of WordPress. Changing the method name may cause program error.
     * Warning! this parent class is marked as private. Please read at wordpress source.
     *
     * @link http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/ tutorial about how to list table data.
     * @link http://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/ another tutorial
     * @link https://codex.wordpress.org/Class_Reference/WP_List_Table wordpress list table class source.
     */
    class RdDownloadLogsListTable extends WPListTable
    {


        use \RdDownloads\App\AppTrait;


        /**
         * {@inheritDoc}
         */
        protected function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'download_name':
                    $output = '';
                    if ($item->user_id == get_current_user_id() || ($item->user_id != get_current_user_id() && current_user_can('edit_others_posts'))) {
                        $output .= '<a href="' . admin_url('admin.php?page=rd-downloads_edit&download_id=' . $item->download_id) . '">';
                    }
                    if (!empty($item->{$column_name})) {
                        $output .= $item->{$column_name};
                    } else {
                        $output .= __('Unknow', 'rd-downloads');
                    }
                    if ($item->user_id == get_current_user_id() || ($item->user_id != get_current_user_id() && current_user_can('edit_others_posts'))) {
                        $output .= '</a>';
                    }
                    return $output;
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
                case 'dl_date':
                case 'dl_date_gmt':
                    $output = '';
                    $datetime_local = strtotime(get_date_from_gmt($item->dl_date_gmt, 'Y-m-d H:i:s'));
                    $datetime_withtimezone = date_i18n('Y-m-d H:i:s (P)', $datetime_local);
                    $output .= '<time datetime="' . esc_attr(mysql2date('c', $item->dl_date_gmt, false)) . '" title="' . esc_attr($datetime_withtimezone) . '">' . $datetime_withtimezone . '</time>';
                    unset($datetime_local, $datetime_withtimezone);
                    return $output;
                default:
                    if (isset($item->{$column_name}) && is_scalar($item->{$column_name})) {
                        return esc_html($item->{$column_name});
                    }
                    return '';
            }
        }// column_default


        /**
         * Column dl_status.
         *
         * @param object $item
         * @return string
         */
        protected function column_dl_status($item)
        {
            $output = '<!-- ' . $item->dl_status . ' -->' . PHP_EOL;

            switch ($item->dl_status) {
                case 'admin_insert':
                    $output .= __('Admin add new item', 'rd-downloads');
                    break;
                case 'admin_update':
                    $output .= __('Admin update an item', 'rd-downloads');
                    break;
                case 'admin_delete':
                    $output .= __('Admin delete an item', 'rd-downloads');
                    break;
                case 'admin_clear_logs':
                    $output .= __('Admin clear logs', 'rd-downloads');
                    break;
                case 'auto_purge_old_logs':
                    $output .= __('Auto purge old logs', 'rd-downloads');
                    break;
                case 'github_autoupdate':
                    $output .= __('GitHub auto update', 'rd-downloads');
                    break;
                case 'user_dl_success':
                    $output .= __('User download success', 'rd-downloads');
                    break;
                case 'user_dl_error':
                    $output .= __('User download failed', 'rd-downloads');
                    break;
                case 'user_dl_banned':
                    $output .= __('User download banned', 'rd-downloads');
                    break;
                case 'user_dl_wr_captcha':// from previous version, keep it here.
                    $output .= __('User enter wrong captcha code', 'rd-downloads');
                    break;
                case 'user_dl_antbotfailed':
                    $output .= __('User has failed to verify antibot', 'rd-downloads');
                    break;
                default:
                    $output .= $item->dl_status;
                    break;
            }// endswitch;

            return $output;
        }// column_dl_status


        /**
         * Get bulk actions
         *
         * @return array return array key and name. example: [key1 => describe how this work]
         */
        protected function get_bulk_actions()
        {
            if (!current_user_can('delete_users')) {
                return [];
            }

            $this->getOptions();
            global $rd_downloads_options;

            $actions = [];
            $actions['clearlogs'] = __('Clear', 'rd-downloads');

            return $actions;
        }// get_bulk_actions


        /**
         * {@inheritDoc}
         */
        public function get_columns()
        {
            $columns = [
                'download_name' => __('Downloads name', 'rd-downloads'),
                'user_id' => __('User', 'rd-downloads'),
                'dl_status' => __('Status', 'rd-downloads'),
                'dl_ip' => __('IP Address', 'rd-downloads'),
                'dl_user_agent' => __('User agent', 'rd-downloads'),
                'dl_date_gmt' => __('Date', 'rd-downloads'),
            ];

            return $columns;
        }// get_columns


        /**
         * {@inheritDoc}
         */
        protected function get_sortable_columns()
        {
            $sortable_columns = [
                'download_name' => ['download_name', false],
                'dl_id' => ['dl_id', false],
                'user_id' => ['user_id', false],
                'dl_ip' => ['dl_ip', false],
                'dl_user_agent' => ['dl_user_agent', false],
                'dl_date_gmt' => ['dl_date_gmt', false],
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
            if (!current_user_can('edit_others_posts')) {
                $filter_user_id = get_current_user_id();
            }
            $filter_download_id = (isset($_REQUEST['filter_download_id']) && trim($_REQUEST['filter_download_id']) != null ? trim($_REQUEST['filter_download_id']) : null);
            $search = (isset($_REQUEST['s']) && !empty(trim($_REQUEST['s'])) ? trim($_REQUEST['s']) : null);

            // get result using `RdDownloadLogs` class.
            $RdDownloadLogs = new RdDownloadLogs();
            $options['*select'] = 'COUNT(dl_id)';
            $options['*return_prepare'] = true;
            if (!empty($search)) {
                $options['*search'] = $search;
            }
            $sql = $RdDownloadLogs->get($options);
            unset($search);

            // all items
            $class = ($filter_download_id == null && $filter_user_id == null ? ' class="current"' : '');
            $views['all'] = '<a' . $class . ' href="' . esc_url(remove_query_arg(['filter_download_id', 'filter_user_id'])) . '">' . __('All', 'rd-downloads') . ' <span class="count">(' . $wpdb->get_var($sql) . ')</span></a>';
            unset($class);

            // filtered user
            if ($filter_user_id != null) {
                $User = get_user_by('ID', $filter_user_id);
                $options['user_id'] = $filter_user_id;
                $sqlFiltered = $RdDownloadLogs->get($options);
                /* translators: %s: Link to edit user. */
                $views['filtered_user'] = '<strong>' . sprintf(__('Filtered user: %s', 'rd-downloads'), '<a href="' . esc_url(get_edit_user_link($filter_user_id)) . '" target="editUser">' . $User->display_name . ' <span class="count">(' . $wpdb->get_var($sqlFiltered) . ')</span></a>') . '</strong>';
                unset($class, $options['user_id'], $sqlFiltered, $User);

                $views['reset_filtered_user'] = '<a href="' . esc_url(remove_query_arg('filter_user_id')) . '">' . __('Reset filtered user', 'rd-downloads') . '</a>';
            }

            // filtered download_id
            if ($filter_download_id != null) {
                $options['download_id'] = $filter_download_id;
                $sqlFiltered = $RdDownloadLogs->get($options);
                /* translators: %s: Link to edit download. */
                $views['filtered_download'] = '<strong>' . sprintf(__('Filtered download: %s', 'rd-downloads'), '<a href="' . esc_url(admin_url('admin.php?page=rd-downloads_edit&download_id=' . $filter_download_id)) . '" target="editDownloads"> <span class="count">(' . $wpdb->get_var($sqlFiltered) . ')</span></a>') . '</strong>';
                unset($class, $options['user_id'], $sqlFiltered, $User);

                $views['reset_filtered_download'] = '<a href="' . esc_url(remove_query_arg('filter_user_id')) . '">' . __('Reset filtered download', 'rd-downloads') . '</a>';
            }

            unset($filter_download_id, $filter_user_id, $options, $RdDownloadLogs);
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

            if (isset($item->download_id) && !empty($item->download_id)) {
                $actions['filter_download'] = sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(add_query_arg('filter_download_id', $item->download_id)),
                    __('Filter download', 'rd-downloads')
                );
            }

            $actions['filter_user'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(add_query_arg('filter_user_id', $item->user_id)),
                __('Filter user', 'rd-downloads')
            );

            return $this->row_actions($actions);
        }// handle_row_actions


        /**
         * prepare data and items
         *
         * @global \wpdb $wpdb
         * @param array $options available options: user_id, download_id, search, sort (column name), order (ascending descending)
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
            $RdDownloadLogs = new RdDownloadLogs();
            // prepare options for the `listItems()` method.
            $listItemsOptions = [];
            if (isset($options['user_id'])) {
                $listItemsOptions['user_id'] = $options['user_id'];
            }
            if (isset($options['download_id'])) {
                $listItemsOptions['download_id'] = $options['download_id'];
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
            $per_page = $this->get_items_per_page('rddownloads_logs_items_perpage');
            $listItemsOptions['*per_page'] = $per_page;
            $listItems = $RdDownloadLogs->listItems($listItemsOptions);
            unset($listItemsOptions, $RdDownloadLogs, $sortable);

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
            echo '<tr class="rd-download-logs_dl_id_' . $item->dl_id . '">';
            $this->single_row_columns($item);
            echo '</tr>';
        }// single_row


    }
}
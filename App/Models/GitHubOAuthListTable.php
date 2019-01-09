<?php
/**
 * List GitHub repositories in WordPress list table class.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Models;

if (!class_exists('\\RdDownloads\\App\\Models\\GitHubOAuthListTable')) {
    /**
     * List data into table.
     * Warning! Do not modify method name because they are extended from WP_List_Table class of WordPress. Changing the method name may cause program error.
     * Warning! this parent class is marked as private. Please read at wordpress source.
     *
     * @link http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/ tutorial about how to list table data.
     * @link http://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/ another tutorial
     * @link https://codex.wordpress.org/Class_Reference/WP_List_Table wordpress list table class source.
     */
    class GitHubOAuthListTable extends WPListTable
    {


        /**
         * Display webhook table column (td).
         *
         * @param object $item
         * @param string $classes
         * @param string $data
         * @param string $primary
         */
        public function _column_webhook($item, $classes, $data, $primary)
        {
            $output = '<td';
            $output .= ' class="' . $classes . ' rddownloads_githubrepo_webhook"';
            $output .= ' ' . $data;
            $output .= '>';
            $output .= $this->column_default($item, 'webhook');
            $output .= $this->handle_row_actions($item, 'webhook', $primary);
            $output .= '</td>' . PHP_EOL;

            return $output;
        }// _column_webhook


        /**
         * {@inheritDoc}
         */
        protected function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'archived':
                    if (isset($item->node->isArchived) && $item->node->isArchived === false) {
                        return '<i class="fas fa-times"></i> ' . __('No', 'rd-downloads');
                    } else {
                        return '<i class="fas fa-check"></i> ' . __('Yes', 'rd-downloads');
                    }
                case 'namewithowner':
                    return '<a href="' . $item->node->url . '" target="github_repository">' . esc_html($item->node->nameWithOwner) . '</a>';
                case 'webhook':
                    if (isset($item->node->isArchived) && $item->node->isArchived === false) {
                        return '<a class="rddownloads_githubrepo_webhook_check"><i class="rddownloads_icon-webhook-status fas fa-question"></i> ' . __('Check this.', 'rd-downloads') . '</a>';
                    }
                default:
                    if (isset($item->node->{$column_name}) && is_scalar($item->node->{$column_name})) {
                        return esc_html($item->node->{$column_name});
                    }
                    return '';
            }// endswitch;
        }// column_default


        /**
         * {@inheritDoc}
         */
        public function get_columns()
        {
            return [
                'namewithowner' => __('Repository name', 'rd-downloads'),
                'archived' => __('Archived', 'rd-downloads'),
                'webhook' => __('Webhook', 'rd-downloads'),
            ];
        }// get_columns


        /**
         * {@inheritDoc}
         */
        protected function get_column_info()
        {
            // prepare columns
            $columns = $this->get_columns();
            $hidden = [];
            $sortable = $this->get_sortable_columns();
            $primary = $this->get_primary_column_name();
            $this->_column_headers = [$columns, $hidden, $sortable, $primary];
            unset($columns, $hidden);

            return $this->_column_headers;
        }// get_column_info


        /**
         * {@inheritDoc}
         */
        protected function get_default_primary_column_name()
        {
            return 'namewithowner';
        }// get_default_primary_column_name


        /**
         * {@inheritDoc}
         */
        protected function get_table_classes()
        {
            $default = parent::get_table_classes();
            $output = [];
            if (is_array($default)) {
                $output = array_merge($output, $default, ['rddownloads_repo_list-table']);
            }
            unset($default);
            return $output;
        }// get_table_classes


        /**
         * {@inheritDoc}
         *
         * @param array $options Accepted array keys: Github (\RdDownloads\App\Libraries\Github class), accessToken, userId
         * @return array Return empty array if failed to get repository, return array with responseHeader, responseBody keys if success get repositories.
         */
        public function prepare_items(array $options = [])
        {
            if (isset($options['Github'])) {
                $Github = $options['Github'];
            } else {
                $Github = new \RdDownloads\App\Libraries\Github();
            }

            if (
                !isset($options['accessToken']) ||
                (
                    isset($options['accessToken']) &&
                    (
                        !is_string($options['accessToken']) ||
                        empty($options['accessToken'])
                    )
                )
            ) {
                return [];
            }

            $headers = [];
            $headers['Authorization'] = 'token ' . $options['accessToken'];
            $headers['Accept'] = 'application/json';

            $graphQLAllRepos = $Github->graphQLAllRepositories();
            $postBody = [
                'query' => str_replace(['%after%', '%before%'], '', $graphQLAllRepos)
            ];

            $cacheKey = 'rd-downloads.github-connect.github-repositories.blog-id-' . get_current_blog_id()
                . '.user-id-' . (isset($options['userId']) ? $options['userId'] : get_current_user_id())
                . '.apiheadersbody-'
                . md5(
                    maybe_serialize($headers)
                    . wp_json_encode($postBody)
                );
            $SimpleCache = new \RdDownloads\App\Libraries\Cache();
            $cacheResult = $SimpleCache->getInstance()->get($cacheKey);

            $output = [];

            if ($cacheResult === false || !is_array($cacheResult) || !isset($cacheResult['header']) || !isset($cacheResult['body'])) {
                $i = 1;
                $end = false;
                $endCursor = '';
                $responseHeader = '';
                $responseBody = '';

                // use do..while to get items from multiple pages. there is loop limited by `$i` variable.
                do {
                    if (!empty($endCursor)) {
                        $postBody = [
                            'query' => str_replace(['%after%', '%before%'], ['after: "' . $endCursor . '", ', ''], $graphQLAllRepos)
                        ];
                    }

                    $response = $Github->apiV4Request($headers, $postBody);

                    if (empty($responseHeader) && isset($response['header'])) {
                        $responseHeader = $response['header'];
                    }

                    if (empty($responseBody) && isset($response['body'])) {
                        // if first loop, set `$responseBody` variable.
                        $responseBody = $response['body'];
                    } else {
                        if (
                            isset($responseBody->data->viewer->repositories->edges) &&
                            is_array($responseBody->data->viewer->repositories->edges) &&
                            isset($response['body']->data->viewer->repositories->edges) &&
                            is_array($response['body']->data->viewer->repositories->edges)
                        ) {
                            $responseBody->data->viewer->repositories->edges = array_merge(
                                $responseBody->data->viewer->repositories->edges,
                                $response['body']->data->viewer->repositories->edges
                            );
                        }
                    }

                    if (isset($response['body']->data->viewer->repositories->pageInfo->endCursor)) {
                        $endCursor = $response['body']->data->viewer->repositories->pageInfo->endCursor;
                    } else {
                        $end = true;
                    }

                    if (
                        !isset($response['body']->data->viewer->repositories->pageInfo->hasNextPage) ||
                        (
                            isset($response['body']->data->viewer->repositories->pageInfo->hasNextPage) &&
                            $response['body']->data->viewer->repositories->pageInfo->hasNextPage === false
                        )
                    ) {
                        $end = true;
                    }

                    $i++;

                    if ($i > 100) {
                        $end = true;
                    }

                    unset($response);
                }
                while($end == false);

                unset($end, $endCursor, $i);

                if (isset($responseHeader['status-int']) && $responseHeader['status-int'] >= 200 && $responseHeader['status-int'] < 300) {
                    $cacheLifetime = apply_filters('rddownloads_cachelifetime_githuboauth_repositories', (3 * 60 * 60));// hours * minutes * seconds = total seconds.
                    $SimpleCache->getInstance()->save($cacheKey, ['header' => $responseHeader, 'body' => $responseBody], $cacheLifetime);
                    unset($cacheLifetime);
                }
            } else {
                $responseHeader = $cacheResult['header'];
                $responseBody = $cacheResult['body'];
                $output['responseCached'] = true;
            }// endif $cacheResult

            unset($cacheKey, $cacheResult, $SimpleCache);

            $output['responseHeader'] = $responseHeader;
            $output['responseBody'] = $responseBody;

            // create pagination
            $this->set_pagination_args([
                'total_items' => (isset($responseBody->data->viewer->repositories->totalCount) ? $responseBody->data->viewer->repositories->totalCount : 0),
                'per_page'    => (isset($responseBody->data->viewer->repositories->edges) && is_array($responseBody->data->viewer->repositories->edges) ? count($responseBody->data->viewer->repositories->edges) : 0)
            ]);

            if (isset($responseBody->data->viewer->repositories->edges)) {
                $this->items = $responseBody->data->viewer->repositories->edges;
            }

            unset($Github, $graphQLAllRepos, $headers, $postBody, $responseBody, $responseHeader);
            return $output;
        }// prepare_items


        /**
         * {@inheritDoc}
         */
        public function single_row($item)
        {
            $output = '<tr';
            $output .= ' data-namewithowner="' . esc_attr($item->node->nameWithOwner) . '"';
            $output .= ' data-url="' . esc_attr($item->node->url) . '"';
            $output .= ' data-isarchived="' . ($item->node->isArchived === false ? 'false' : 'true') . '"';
            $output .= '>' . PHP_EOL;
            echo $output;

            $this->single_row_columns($item);

            $output = '</tr>' . PHP_EOL;
            echo $output;
        }// single_row


    }
}
<?php
/**
 * Rundiz Downloads table (rd_downloads).
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Models;

if (!class_exists('\\RdDownloads\\App\\Models\\RdDownloads')) {
    class RdDownloads
    {


        /**
         * Get one row of data from rd_downloads and related tables.
         * 
         * This method use `get_row()` of `\wpdb` class.
         * 
         * @global \wpdb $wpdb
         * @param array $options The conditions to get data. The array key must be map to table fields.<br>
         *                                      Additional array keys:<br>
         *                                      *select = The fields to select in sql statement. This must be string.<br>
         *                                      *conditions = Custom conditions. This value must array and must contain "statement" and "args" in keys. To use conditions, the *select key is not recommended to use.<br>
         *                                      *search = A search keyword. To use search keywords, the *select key is not recommended to use.<br>
         *                                      *return_prepare = Set this to true to just call `prepare()` and return it without execute `get_row()`.<br>
         *                                      Example:
         * <pre>
         * array(
         *     '*select' => 'COUNT(download_id)',
         *     '*conditions' => array(
         *         'statement' => '`download_type` = %d OR `user_id` = %d',// the parenthesis will be cover automatically.
         *         'args' => [0, 1],
         *     ),
         *     '*search' => 'myfile.zip',
         *     '*return_prepare' => false,
         * )
         * </pre>
         * @return object|null|void|string Database query result in format specified by $output or null on failure. If *return_prepare in options was set, it will return prepared statement string.
         */
        public function get(array $options = [])
        {
            global $wpdb;

            // sql
            $prepareValues = [];
            $sql = 'SELECT ';
            if (isset($options['*select'])) {
                // if there is custom select fields.
                $sql .= $options['*select'];
                unset($options['*select']);
            } else {
                // if there is no custom select fields.
                $sql .= $this->getSelectFields();
            }
            $sql .= ' FROM `' . $wpdb->prefix . 'rd_downloads`';
            $sql .= ' LEFT JOIN `' . $wpdb->users . '` ON `' . $wpdb->prefix . 'rd_downloads`.`user_id` = `' . $wpdb->users . '`.`ID`';

            $sql .= ' WHERE %d';
            $prepareValues[] = 1;

            // custom conditions.
            if (
                isset($options['*conditions']) && 
                isset($options['*conditions']['statement']) && 
                isset($options['*conditions']['args']) &&
                is_string($options['*conditions']['statement']) &&
                is_array($options['*conditions']['args'])
            ) {
                $sql .= ' AND (' . $options['*conditions']['statement'] . ')';
                $prepareValues = $prepareValues + $options['*conditions']['args'];
                unset($options['*conditions']);
            }

            // search keywords.
            if (isset($options['*search'])) {
                $sql .= ' AND ' . $this->getSearchFields();
                foreach ($this->getSearchFields(false) as $item) {
                    $prepareValues[] = '%'.$wpdb->esc_like($options['*search']).'%';
                }// endforeach;
                unset($item);
                unset($options['*search']);
            }

            // put the rest of fields into statement.
            $fieldsOptions = $this->populateFieldOptions($options);
            if (!empty($fieldsOptions) && is_array($fieldsOptions)) {
                $sql .= ' AND (';
                // Get array keys
                $arrayKeys = array_keys($fieldsOptions);
                // Fetch last array key
                $lastArrayKey = array_pop($arrayKeys);
                foreach ($fieldsOptions as $name => $value) {
                    if (is_scalar($name) && strpos($name, '*') === false) {
                        $sql .= '`' . $name . '` = %s';
                        $prepareValues[] = $value;
                        if ($name !== $lastArrayKey) {
                            $sql .= ' AND ';
                        }
                    }
                }// endforeach;
                unset($name, $value);
                $sql .= ')';
                unset($arrayKeys, $lastArrayKey);
            }
            unset($fieldsOptions);

            $prepared = $wpdb->prepare($sql, $prepareValues);
            unset($prepareValues, $sql);

            if (isset($options['*return_prepare']) && $options['*return_prepare'] === true) {
                return $prepared;
            }

            $result = $wpdb->get_row($prepared);

            return $result;
        }// get


        /**
         * Get searchable fields.
         * 
         * @global \wpdb $wpdb
         * @param boolean $returnStatement Set to false to return as array fields, set to true to generate SQL statement string.
         * @return array|string Return array values of searchable fields or return SQL statement string with parenthesis.
         */
        public function getSearchFields($returnStatement = true)
        {
            global $wpdb;

            $fields = [
                '`download_name`',
                '`download_admin_comment`',
                '`download_github_name`',
                '`download_url`',
                '`download_related_path`',
                '`download_file_name`',
                '`' . $wpdb->users . '`.`user_login`',
                '`' . $wpdb->users . '`.`user_nicename`',
                '`' . $wpdb->users . '`.`user_email`',
                '`' . $wpdb->users . '`.`display_name`',
            ];

            if ($returnStatement === true) {
                $statement = '(';
                // Get array keys
                $arrayKeys = array_keys($fields);
                // Fetch last array key
                $lastArrayKey = array_pop($arrayKeys);
                foreach ($fields as $key => $item) {
                    $statement .= $item . ' LIKE %s';
                    if ($key !== $lastArrayKey) {
                        $statement .= ' OR ';
                    }
                }// endforeach;
                unset($arrayKeys, $item, $key, $lastArrayKey);
                $statement .= ')';

                return $statement;
            } else {
                return $fields;
            }
        }// getSearchFields


        /**
         * Get select fields.
         * 
         * @global \wpdb $wpdb
         * @param boolean $returnStatement Set to false to return as array fields, set to true to generate SQL statement string.
         * @return array|string Return array values of select fields or return SQL statement string.
         */
        public function getSelectFields($returnStatement = true)
        {
            global $wpdb;

            $fields = [
                '`' . $wpdb->prefix . 'rd_downloads`.*',
                '`' . $wpdb->users . '`.`ID`',
                '`' . $wpdb->users . '`.`user_login`',
                '`' . $wpdb->users . '`.`user_nicename`',
                '`' . $wpdb->users . '`.`user_email`',
                '`' . $wpdb->users . '`.`user_registered`',
                '`' . $wpdb->users . '`.`display_name`',
            ];

            if ($returnStatement === true) {
                return implode(', ', $fields);
            } else {
                return $fields;
            }
        }// getSelectFields


        /**
         * Increase download count.
         * 
         * @global \wpdb $wpdb
         * @param integer $download_id The download_id field data.
         * @return boolean Return true on success, false on failure.
         */
        public function increaseDownloadCount($download_id)
        {
            global $wpdb;

            $sql = 'UPDATE `' . $wpdb->prefix . 'rd_downloads` SET `download_count` = `download_count` + 1 WHERE `download_id` = %d';

            $updateResult = $wpdb->query($wpdb->prepare($sql, [$download_id]));

            unset($sql);

            if ($updateResult !== false) {
                return true;
            }
            return false;
        }// increaseDownloadCount


        /**
         * List items from rd_downloads and related tables.
         * 
         * This method use `get_results()` of `\wpdb` class.
         * 
         * @global \wpdb $wpdb
         * @param array $options The conditions to get data. The array key must be map to table fields.<br>
         *                                      Additional array keys:<br>
         *                                      *conditions = Custom conditions. This value must array and must contain "statement" and "args" in keys. To use conditions, the *select key is not recommended to use.<br>
         *                                      *search = A search keyword. To use search keywords, the *select key is not recommended to use.<br>
         *                                      *sort = A field to sort.<br>
         *                                      *order = Order the sorting (ASC, DESC)<br>
         *                                      *current_page = The current page number.<br>
         *                                      *per_page = Total item per page.<br>
         *                                      *unlimit = Set this value to true to skip `LIMIT command and *current_page, *per_page will not work.
         *                                      Example:
         * <pre>
         * array(
         *     'download_id' => 1,
         *     'user_id' => 1,
         *     '*conditions' => array(
         *         'statement' => '`download_type` = %d OR `user_id` = %d',// the parenthesis will be cover automatically.
         *         'args' => [0, 1],
         *     ),
         *     '*search' => 'myfile.zip',
         *     '*sort' => 'download_id',
         *     '*order' => 'DESC',
         *     '*current_page' => 1,
         *     '*per_page' => 20,
         *     '*unlimit' => false,// set to true *current_page, *per_page will not work.
         * )
         * </pre>
         * @return array Return array with "total", "results" in key.
         */
        public function listItems(array $options = [])
        {
            global $wpdb;

            // sql
            $prepareValues = [];
            $sql = 'SELECT %*%';
            $sql .= ' FROM `' . $wpdb->prefix . 'rd_downloads`';
            $sql .= ' LEFT JOIN `' . $wpdb->users . '` ON `' . $wpdb->prefix . 'rd_downloads`.`user_id` = `' . $wpdb->users . '`.`ID`';

            $sql .= ' WHERE %d';
            $prepareValues[] = 1;

            // custom conditions.
            if (
                isset($options['*conditions']) && 
                isset($options['*conditions']['statement']) && 
                isset($options['*conditions']['args']) &&
                is_string($options['*conditions']['statement']) &&
                is_array($options['*conditions']['args'])
            ) {
                $sql .= ' AND (' . $options['*conditions']['statement'] . ')';
                $prepareValues = $prepareValues + $options['*conditions']['args'];
                unset($options['*conditions']);
            }

            // search keywords.
            if (isset($options['*search']) && !empty($options['*search'])) {
                $sql .= ' AND ' . $this->getSearchFields();
                foreach ($this->getSearchFields(false) as $item) {
                    $prepareValues[] = '%'.$wpdb->esc_like($options['*search']).'%';
                }// endforeach;
                unset($item);
                unset($options['*search']);
            }

            // put the rest of fields into statement.
            $fieldsOptions = $this->populateFieldOptions($options);
            if (!empty($fieldsOptions) && is_array($fieldsOptions)) {
                $sql .= ' AND (';
                // Get array keys
                $arrayKeys = array_keys($fieldsOptions);
                // Fetch last array key
                $lastArrayKey = array_pop($arrayKeys);
                foreach ($fieldsOptions as $name => $value) {
                    if (is_scalar($name) && strpos($name, '*') === false) {
                        $sql .= '`' . $name . '` = %s';
                        $prepareValues[] = $value;
                        if ($name !== $lastArrayKey) {
                            $sql .= ' AND ';
                        }
                    }
                }// endforeach;
                unset($name, $value);
                $sql .= ')';
                unset($arrayKeys, $lastArrayKey);
            }
            unset($fieldsOptions);

            $total_items = $wpdb->get_var(
                $wpdb->prepare(
                    str_replace(['%*%'], ['COUNT(' . $wpdb->prefix . 'rd_downloads' . '.download_id)'], $sql), 
                    $prepareValues
                )
            );

            // set sort order
            $sort = 'download_id';
            if (isset($options['*sort'])) {
                $sort = $options['*sort'];
            }
            if ($sort == 'user_id') {
                $sort = $wpdb->users . '.display_name';
            }

            $order = 'DESC';
            if (isset($options['*order'])) {
                if (strtolower($options['*order']) == 'asc') {
                    $order = 'ASC';
                }  elseif (strtolower($options['*order']) == 'desc') {
                    $order = 'DESC';
                }
            }

            // get data that sliced per page and sort order
            $sql = str_replace('%*%', '*', $sql);
            // sort order
            $sql .= ' ORDER BY ' . $sort . ' ' . $order;
            if (!isset($options['*unlimit']) || (isset($options['*unlimit']) && $options['*unlimit'] === false)) {
                // sliced per page
                $current_page = 1;
                if (isset($options['*current_page']) && is_numeric($options['*current_page'])) {
                    $current_page = intval($options['*current_page']);
                }
                $per_page = 20;
                if (isset($options['*per_page']) && is_numeric($options['*per_page'])) {
                    $per_page = intval($options['*per_page']);
                }
                $sql .= ' LIMIT ' . (($current_page - 1) * $per_page) . ', ' . $per_page;
                unset($current_page, $per_page);
            }

            $results = $wpdb->get_results(
                $wpdb->prepare($sql, $prepareValues)
            );
            unset($order, $prepareValues, $sort, $sql);

            return [
                'total' => $total_items,
                'results' => $results,
            ];
        }// listItems


        /**
         * Filter out "*" to keep only fields option from many options (including custom options) based on `get()` and `listItems()` methods.
         * 
         * @param array $options The options.
         * @return array Return filtered out any options that contain * mark.
         */
        protected function populateFieldOptions(array $options)
        {
            $output = [];

            foreach ($options as $name => $value) {
                if (is_scalar($name) && strpos($name, '*') === false) {
                    $output[$name] = $value;
                }
            }// endforeach;
            unset($name, $value);

            return $output;
        }// populateFieldOptions


        /**
         * Update data to DB.
         * 
         * @global \wpdb $wpdb
         * @param array $data The associate array match table field to update.
         * @param array $where The associate array match table field to condition the update.
         * @return integer|false Return the number of rows updated, or false on error.
         */
        public function update(array $data, array $where)
        {
            global $wpdb;

            if (isset($data['download_url']) && !isset($data['download_file_name'])) {
                $FileSystem = new \RdDownloads\App\Libraries\FileSystem();
                $fileParts = $FileSystem->getFilePart($data['download_url']);
                if (isset($fileParts['nameext'])) {
                    $data['download_file_name'] = $fileParts['nameext'];
                }
                unset($fileParts, $FileSystem);
            }

            if (!isset($data['download_update'])) {
                $data['download_update'] = current_time('mysql');
            }

            if (!isset($data['download_update_gmt'])) {
                $data['download_update_gmt'] = current_time('mysql', true);
            }

            unset($data['download_create'], $data['download_create_gmt']);

            return $wpdb->update(
                $wpdb->prefix . 'rd_downloads',
                $data,
                $where
            );
        }// update


    }
}
<?php
/**
 * File browser (also working with delete and upload).
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Downloads\Xhr;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Downloads\\Xhr\\XhrFileBrowser')) {
    class XhrFileBrowser extends \RdDownloads\App\Controllers\XhrBased implements \RdDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Banned file names. write it in lower case only.
         * @var array List of banned file name that will not be display.
         */
        protected $bannedFileNames = ['.htaccess'];


        /**
         * Ajax browse files.
         *
         * @global \wpdb $wpdb
         */
        public function browseFiles()
        {
            $this->commonAccessCheck(['get'], ['rd-downloads_ajax-file-browser-nonce', 'security']);

            $output = [];
            $responseStatus = 200;

            $target = filter_input(INPUT_GET, 'target', FILTER_UNSAFE_RAW);
            if (stripos($target, '..') !== false) {
                $target = '';
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('Hacking attempt!', 'rd-downloads');
                wp_send_json($output, 403);
            }

            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                $output['debugTarget'] = $target;
            }

            $wp_upload_dir = wp_upload_dir();
            if (
                is_array($wp_upload_dir) &&
                array_key_exists('basedir', $wp_upload_dir) &&
                array_key_exists('baseurl', $wp_upload_dir) &&
                is_dir($wp_upload_dir['basedir'] . '/' . $target)
            ) {
                $output['list'] = [];
                $Fi = new \FilesystemIterator(
                    realpath($wp_upload_dir['basedir'] . '/' . $target),
                    \FilesystemIterator::SKIP_DOTS
                );
                $folders = [];
                $files = [];
                $whereFiles = [];
                foreach ($Fi as $FileInfo) {
                    if ($FileInfo->isDir()) {
                        $folders[$FileInfo->getFilename()]['id'] = md5($FileInfo->getPathname());
                        $folders[$FileInfo->getFilename()]['basename'] = $FileInfo->getBasename();// base name.
                        $folders[$FileInfo->getFilename()]['filename'] = $FileInfo->getFilename();// name only.
                        $folders[$FileInfo->getFilename()]['path'] = $FileInfo->getPath();// seems to be path of wp upload dir + target.
                        $folders[$FileInfo->getFilename()]['pathname'] = $FileInfo->getPathname();// full path to file.
                        $folders[$FileInfo->getFilename()]['size'] = $FileInfo->getSize();// file size in bytes.
                        $folders[$FileInfo->getFilename()]['isFile'] = false;
                        $folders[$FileInfo->getFilename()]['url'] = $wp_upload_dir['baseurl'] . $target . '/' . $FileInfo->getFilename();
                        $folders[$FileInfo->getFilename()]['previousTarget'] = $target . '/' . $FileInfo->getFilename();
                        $folders[$FileInfo->getFilename()]['relatedPath'] = ltrim($target . '/' . $FileInfo->getFilename(), '/');
                    } else {
                        if (!in_array(strtolower($FileInfo->getFilename()), $this->bannedFileNames)) {
                            $files[$FileInfo->getFilename()]['id'] = md5($FileInfo->getPathname());
                            $files[$FileInfo->getFilename()]['basename'] = $FileInfo->getBasename();// base name.
                            $files[$FileInfo->getFilename()]['filename'] = $FileInfo->getFilename();// name only.
                            $files[$FileInfo->getFilename()]['path'] = $FileInfo->getPath();// seems to be path of wp upload dir + target.
                            $files[$FileInfo->getFilename()]['pathname'] = $FileInfo->getPathname();// full path to file.
                            $files[$FileInfo->getFilename()]['size'] = $FileInfo->getSize();// file size in bytes.
                            $files[$FileInfo->getFilename()]['isFile'] = true;
                            $files[$FileInfo->getFilename()]['url'] = $wp_upload_dir['baseurl'] . $target . '/' . $FileInfo->getFilename();
                            $files[$FileInfo->getFilename()]['previousTarget'] = $target . '/' . $FileInfo->getFilename();
                            $files[$FileInfo->getFilename()]['relatedPath'] = ltrim($target . '/' . $FileInfo->getFilename(), '/');
                            if (
                                stripos($target, '/rd-downloads') !== false &&
                                $target . '/' . $FileInfo->getFilename() != '/rd-downloads/index.html' &&
                                current_user_can('upload_files')
                            ) {
                                $files[$FileInfo->getFilename()]['isDeletable'] = true;
                                $whereFiles[] = $wp_upload_dir['baseurl'] . $target . '/' . $FileInfo->getFilename();
                            } else {
                                $files[$FileInfo->getFilename()]['isDeletable'] = false;
                            }
                        }
                    }
                }// endforeach;
                unset($FileInfo);

                // search for file that exists in db.
                if (!empty($whereFiles)) {
                    global $wpdb;
                    $whereInPlaceholder = implode(', ', array_fill(0, count($whereFiles), '%s'));// https://stackoverflow.com/a/10634225/128761
                    $sql = 'SELECT `user_id`, `download_id`, `download_type`, `download_url` FROM `' . $wpdb->prefix . 'rd_downloads` WHERE `download_type` = 0 AND `download_url` IN (' . $whereInPlaceholder . ')';
                    $searchFilesResults = $wpdb->get_results(
                        $wpdb->prepare(
                            $sql,
                            $whereFiles
                        )
                    );
                    unset($whereInPlaceholder);

                    if (defined('WP_DEBUG') && WP_DEBUG === true) {
                        $output['debugSQL'] = $sql;// before executed sql statement (contain %s placeholder for prepare).
                        $output['debugLastQuery'] = $wpdb->last_query;// executed sql statement.
                    }
                    unset($sql);

                    if (count($searchFilesResults) > 0 && (is_array($searchFilesResults) || is_object($searchFilesResults))) {
                        foreach ($searchFilesResults as $row) {
                            foreach ($files as $key => $item) {
                                if (isset($item['isDeletable']) && $item['isDeletable'] === true) {
                                    if (isset($item['url']) && $item['url'] == $row->download_url) {
                                        $files[$key]['isDeletable'] = false;
                                        $files[$key]['isLinkedDownloadsData'] = true;
                                        if (
                                            $row->user_id == get_current_user_id() ||
                                            (
                                                $row->user_id != get_current_user_id() &&
                                                current_user_can('edit_others_posts')
                                            )
                                        ) {
                                            $files[$key]['editUrl'] = admin_url('admin.php?page=rd-downloads_edit&download_id=' . $row->download_id);
                                        }
                                    }
                                }
                            }// endforeach $files;
                            unset($item, $key);
                        }// endforeach $searchFilesResults;
                    }
                    unset($searchFilesResults);
                }//endif $whereFiles;

                ksort($folders);
                ksort($files);
                $output['list'] = $folders + $files;
                unset($Fi, $files, $folders, $whereFiles);
            }// endif $wp_upload_dir
            unset($target, $wp_upload_dir);

            wp_send_json($output, $responseStatus);
        }// browseFiles


        /**
         * Change upload folder.
         *
         * @access protected Do not access this method directly, it is called from `add_action()`.
         * @param array $dir
         * @return array
         */
        public function changeUploadDir($dir)
        {
            if (is_array($dir)) {
                $dir['path'] = $dir['basedir'] . DIRECTORY_SEPARATOR . 'rd-downloads' . DIRECTORY_SEPARATOR . current_time('Y') . DIRECTORY_SEPARATOR . current_time('m');
                $dir['url'] = $dir['baseurl'] . '/rd-downloads/' . current_time('Y') . '/' . current_time('m');
                $dir['subdir'] = '/rd-downloads/' . current_time('Y') . '/' . current_time('m');
            }

            return $dir;
        }// changeUploadDir


        /**
         * Ajax delete an uploaded file.
         *
         * @global \wpdb $wpdb
         */
        public function deleteFile()
        {
            $this->commonAccessCheck(['post'], ['rd-downloads_ajax-file-browser-nonce', 'security']);

            $output = [];
            $responseStatus = 200;

            $target = filter_input(INPUT_POST, 'target', FILTER_UNSAFE_RAW);
            if (stripos($target, '..') !== false) {
                $target = '';
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('Hacking attempt!', 'rd-downloads');
                wp_send_json($output, 403);
            }
            $download_id = filter_input(INPUT_POST, 'download_id', FILTER_SANITIZE_NUMBER_INT);
            if ($download_id == '0' || !is_numeric($download_id)) {
                $download_id = '';
            }

            $output['target'] = $target;

            // search for banned files.
            foreach ($this->bannedFileNames as $bannedFile) {
                if (stripos($target, $bannedFile) !== false) {
                    $responseStatus = 403;
                    $output['form_result_class'] = 'notice-error';
                    /* translators: %s: list of banned files */
                    $output['form_result_msg'] = sprintf(__('Unable to delete file that is in system file (%s).', 'rd-downloads'), '<strong>' . $bannedFile . '</strong>');
                    $disallowDelete = true;
                    break;
                }
            }// endforeach;
            unset($bannedFile);

            if (!current_user_can('upload_files')) {
                $responseStatus = 403;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('You do not have permission to access this page.');
                $disallowDelete = true;
            }

            // search for deleting /rd-downloads/index.html
            if (
                (
                    !isset($disallowDelete) ||
                    (
                        isset($disallowDelete) &&
                        $disallowDelete === false
                    )
                ) &&
                (
                    strtolower($target) == '/rd-downloads/index.html' ||
                    strtolower($target) == 'rd-downloads/index.html'
                )
            ) {
                $responseStatus = 403;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('Unable to delete index file that is preventing directory browsing.', 'rd-downloads');
                $disallowDelete = true;
            }

            if (!isset($disallowDelete) || (isset($disallowDelete) && $disallowDelete === false)) {
                $wp_upload_dir = wp_upload_dir();
                if (
                    is_array($wp_upload_dir) &&
                    array_key_exists('basedir', $wp_upload_dir) &&
                    array_key_exists('baseurl', $wp_upload_dir) &&
                    is_file($wp_upload_dir['basedir'] . '/' . $target) &&
                    stripos($target, '/rd-downloads') !== false
                ) {
                    // if file was found.
                    $output['deleteFile'] = realpath($wp_upload_dir['basedir'] . '/' . $target);
                    $output['deleteUrl'] = $wp_upload_dir['baseurl'] . $target;

                    global $wpdb;
                    $itemRow = $wpdb->get_row(
                        $wpdb->prepare(
                            'SELECT `download_id`, `download_type`, `download_url` FROM `' . $wpdb->prefix . 'rd_downloads` WHERE `download_type` = 0 AND `download_url` = %s',
                            $output['deleteUrl']
                        )
                    );
                    if (is_object($itemRow) && property_exists($itemRow, 'download_id')) {
                        // if found item in db. unable to delete this, never!
                        $output['debug'] = $itemRow;
                        $output['download_id'] = $itemRow->download_id;
                        $output['unlink'] = false;
                        $output['deleted'] = false;

                        $responseStatus = 403;
                        $output['form_result_class'] = 'notice-error';
                        $output['form_result_msg'] = sprintf(
                            /* translators: %1$s: Open link tag, %2$s Close link tag. */
                            __('Unable to delete the selected file. The file is already in use. Please %1$sedit%2$s this downloads instead..', 'rd-downloads'),
                            '<a href="' . admin_url('admin.php?page=rd-downloads_edit&download_id=' . $itemRow->download_id) . '">',
                            '</a>'
                        );
                    } else {
                        // if in the conditions that is able to delete the file.
                        $FileSystem = new \RdDownloads\App\Libraries\FileSystem();
                        $unlink = $FileSystem->deleteFile($wp_upload_dir['basedir'] . '/' . $target);
                        $output['unlink'] = $unlink;
                        $output['deleted'] = !is_file($wp_upload_dir['basedir'] . '/' . $target);
                        unset($unlink);
                    }
                    unset($itemRow);
                } elseif (
                    is_array($wp_upload_dir) &&
                    array_key_exists('basedir', $wp_upload_dir) &&
                    array_key_exists('baseurl', $wp_upload_dir) &&
                    !is_file($wp_upload_dir['basedir'] . '/' . $target)
                ) {
                    // if file was not found.
                    $responseStatus = 404;
                    $output['form_result_class'] = 'notice-error';
                    /* translators: %s: The selected URL to show in error. */
                    $output['form_result_msg'] = sprintf(__('The selected file was not found (%s).', 'rd-downloads'), $wp_upload_dir['baseurl'] . $target);
                } elseif (stripos($target, '/rd-downloads') === false) {
                    // does not in /rd-downloads folder
                    $responseStatus = 403;
                    $output['form_result_class'] = 'notice-error';
                    /* translators: %1$s: /rd-downloads path string, %2$s: The selected URL to show in error. */
                    $output['form_result_msg'] = sprintf(__('Unable to delete file that is not in %1$s folder (%2$s).', 'rd-downloads'), '<strong>/rd-downlods</strong>', $wp_upload_dir['baseurl'] . $target);
                }// endif $wp_upload_dir
                unset($wp_upload_dir);
            }// endif; $disallowDelete
            unset($target);

            wp_send_json($output, $responseStatus);
        }// deleteFile


        /**
         * Use server side to get remote file data such as size, content-type.
         *
         * This is to avoid cross origin blocked via ajax.
         */
        public function getRemoteFileData()
        {
            $this->commonAccessCheck(['get'], ['rd-downloads_ajax-file-browser-nonce', 'security']);

            $output = [];
            $responseStatus = 200;
            $remote_file = trim(filter_input(INPUT_GET, 'remote_file', FILTER_SANITIZE_URL));

            if (filter_var($remote_file, FILTER_VALIDATE_URL) !== false) {
                $Url = new \RdDownloads\App\Libraries\Url();
                $remoteFileResult = $Url->getRemoteFileInfo($remote_file);
                if (is_array($remoteFileResult)) {
                    $output = $output + $remoteFileResult;
                }
                unset($remoteFileResult, $Url);
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                    $output['debug_remoteurl'] = 'invalid';
                }
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('Invalid URL.', 'rd-downloads');
            }

            wp_send_json($output, $responseStatus);
        }// getRemoteFileData


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdDownloadsBrowseFiles', [$this, 'browseFiles']);
                add_action('wp_ajax_RdDownloadsDeleteFile', [$this, 'deleteFile']);
                add_action('wp_ajax_RdDownloadsUploadFile', [$this, 'uploadFile']);
                add_action('wp_ajax_RdDownloadsGetRemoteFileData', [$this, 'getRemoteFileData']);
            }
        }// registerHooks


        /**
         * Set new file name if it is not safe for web (example: contain Thai character) or add number suffix if file exists.
         *
         * @link https://github.com/Rundiz/upload/blob/version2/Rundiz/Upload/Upload.php Reference.
         * @access protected  Do not access this method directly, it is called from `add_action()`.
         * @param string $name
         * @param string $ext
         * @param string $dir
         * @param callable|null $unique_filename_callback Callback function that generates the unique file name.
         * @return string
         */
        public function safeWebFileName($name, $ext, $dir, $unique_filename_callback)
        {
            // remove file extension (.jpg, .gif, ...).
            $name = str_replace($ext, '', $name);
            // replace multiple spaces to one space.
            $name = preg_replace('#\s+#iu', ' ', $name);
            // replace space to dash.
            $name = str_replace(' ', '-', $name);
            // replace non alpha-numeric to nothing.
            $name = preg_replace('#[^\da-z\-_]#iu', '', $name);
            // replace multiple dashes to one dash.
            $name = preg_replace('#-{2,}#', '-', $name);
            // replace multiple dots to one dot.
            $name = preg_replace('#\.{2,}#', '.', $name);

            $number = 0;
            $round = 0;
            while(file_exists($dir . '/' . $name . $ext)) {
                $newNumber = (int)$number + 1;
                if ($newNumber == '1') {
                    $name .= '-' . $newNumber;
                } else {
                    $name = str_replace('-' . $number, '-' . $newNumber, $name);
                }
                $number = $newNumber;
                $round++;

                if ($round > 10000) {
                    $name .= uniqid().'-'.str_replace('.', '', microtime(true));
                    break;
                }
            }
            unset($newNumber, $number, $round);

            if (empty($name)) {
                // if the name get replaced and empty (I hope not but make sure).
                // set new random name.
                $name = uniqid().'-'.str_replace('.', '', microtime(true));
            }

            return $name . $ext;
        }// safeWebFileName


        /**
         * Ajax upload a file.
         */
        public function uploadFile()
        {
            $this->commonAccessCheck(['post'], ['rd-downloads_ajax-file-browser-nonce', 'security']);

            $output = [];
            $responseStatus = 200;

            if (!current_user_can('upload_files')) {
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('You do not have permission to access this page.');
                wp_send_json($output, 403);
            }

            $download_id = filter_input(INPUT_POST, 'download_id', FILTER_SANITIZE_NUMBER_INT);
            if ($download_id == '0' || !is_numeric($download_id)) {
                $download_id = '';
            }
            $upload_file = (isset($_FILES['upload_file']) ? $_FILES['upload_file'] : null);

            $overrides['action'] = (isset($_POST['action']) ? $_POST['action'] : null);
            add_filter('upload_dir', [$this, 'changeUploadDir']);
            add_filter('wp_unique_filename', [$this, 'safeWebFileName'], 10, 4);
            $uploadResult = wp_handle_upload($upload_file, $overrides);
            remove_filter('upload_dir', [$this, 'changeUploadDir']);
            remove_filter('wp_unique_filename', [$this, 'safeWebFileName']);

            if (isset($uploadResult['error']) && is_scalar($uploadResult['error'])) {
                // if failed to upload.
                $responseStatus = 400;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = $uploadResult['error'];
            } elseif (isset($uploadResult['file']) && isset($uploadResult['url']) && is_file($uploadResult['file'])) {
                // if success.
                // prepare output data to response.
                $wp_upload_dir = wp_upload_dir();
                if (isset($wp_upload_dir['basedir'])) {
                    $output['relatedPath'] = ltrim(str_replace('\\', '/', str_replace(realpath($wp_upload_dir['basedir']), '', realpath($uploadResult['file']))), '/');
                }
                unset($wp_upload_dir);
                $output['download_size'] = filesize($uploadResult['file']);
                $output['download_url'] = $uploadResult['url'];
                $output['parentDir'] = '/rd-downloads/' . current_time('Y') . '/' . current_time('m');
                $output['parentId'] = md5(dirname(realpath($uploadResult['file'])));
                $output['downloadFullPath'] = realpath($uploadResult['file']);
                $output['uploadSuccess'] = true;

                unset($uploadResult);
            } else {
                // other else.
                $responseStatus = 400;
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('Unable to process your request, please reload the page and try again.', 'rd-downloads');
            }

            wp_send_json($output, $responseStatus);
        }// uploadFile


    }
}
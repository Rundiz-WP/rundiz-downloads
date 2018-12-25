<?php
/**
 * Logger
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\Logger')) {
    /**
     * Logger class.
     */
    class Logger
    {


        /**
         * Write log to file.
         * 
         * This will be working only `WP_DEBUG` constant is set to `true`.
         * 
         * @param mixed $content The content to write to log file.
         *                                      Leave empty will write all server variables and super global data such as $_GET, $_POST to the file. 
         *                                      Non scalar content will be convert to string automatically.
         * @param string $fileName The file name without extension.
         */
        public function debugLog($content, $fileName = '')
        {
            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                if (file_exists(plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug') && !is_dir(plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug')) {
                    error_log('Unable to create folder: ' . plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug');
                    return false;
                }

                if (is_dir(plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug') && !wp_is_writable(plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug')) {
                    error_log('The folder ' . plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug' . ' is unable to create files, please set the write permission.');
                    return false;
                }

                if (!file_exists(plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug') && wp_is_writable(plugin_dir_path(RDDOWNLOADS_FILE))) {
                    wp_mkdir_p(plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug');
                }

                if (!is_file(plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug/index.html')) {
                    file_put_contents(plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug/index.html', '');
                }

                // sanitize file section.
                $fileName = preg_replace('#([^a-z\d\.\-_])#miu', '', $fileName);// allowed: a-z, 0-9, ., -, _ characters.
                $fileName = preg_replace('#(\.|\-|_)\\1+#miu', '$1', $fileName);// remove duplicate characters next to it. ( https://stackoverflow.com/a/13977072/128761 reference ).
                if (empty($fileName)) {
                    $fileName = current_time('Ymd-His');
                }
                $fileName .= '.txt';

                $myfile = fopen(plugin_dir_path(RDDOWNLOADS_FILE).'/_logdebug/'.$fileName, 'a');
                unset($fileName);

                if ($myfile === false) {
                    return;
                }

                if ($content == null) {
                    $content = 'This file was generated for debug data while `WP_DEBUG` constant is set to `true`.'."\r\n";
                    $content .= 'This debug data was generated from '.__FILE__.' at `debugLog()` method.'."\r\n";
                    $content .= 'To turn off this debug, set `WP_DEBUG` to `false` or add `return false;` at the top of this method.'."\r\n\r\n";
                    $content .= 'HEADERS data'."\r\n";
                    $content .= var_export(stripslashes_deep(getallheaders()), true);
                    $content .= "\r\n\r\n";
                    $content .= 'GET data'."\r\n";
                    $content .= var_export(stripslashes_deep($_GET), true);
                    $content .= "\r\n\r\n";
                    $content .= 'POST data'."\r\n";
                    $content .= var_export(stripslashes_deep($_POST), true);
                    $content .= "\r\n\r\n";
                    $content .= 'SERVER data'."\r\n";
                    $content .= var_export(stripslashes_deep($_SERVER), true);
                    $content .= "\r\n\r\n";
                } elseif (is_string($content) || is_numeric($content)) {
                    $content = preg_replace('~\R~u', "\r\n", $content);
                    $content .= "\r\n\r\n\r\n";
                } else {
                    $content = preg_replace('~\R~u', "\r\n", var_export($content, true));
                    $content .= "\r\n\r\n\r\n";
                }

                fwrite($myfile, $content);
                unset($content);
                fclose($myfile);
                unset($myfile);
            }
        }// debugLog


        /**
         * Write log to file.
         * 
         * This will be working only `WP_DEBUG` constant is set to `true`.
         * 
         * @see RdDownloads\App\Libraries\Logger::debugLog()
         * @param mixed $content The content to write to log file.
         *                                      Leave empty will write all server variables and super global data such as $_GET, $_POST to the file. 
         *                                      Non scalar content will be convert to string automatically.
         * @param string $fileName The file name without extension.
         */
        public static function staticDebugLog($content, $fileName = '')
        {
            $thisClass = new self();
            return $thisClass->debugLog($content, $fileName);
        }// debugLog


    }
}
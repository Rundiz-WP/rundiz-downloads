<?php
/**
 * File system.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\FileSystem')) {
    class FileSystem
    {


        /**
         * Delete a file.
         * 
         * @param string $path File path.
         * @return boolean Return true on success, false on failure.
         */
        public function deleteFile($path)
        {
            if (
                is_file($path) &&
                wp_is_writable($path)
            ) {
                return @unlink($path);
            }

            return false;
        }// deleteFile


        /**
         * Get parts of file.
         * 
         * @param string $file URL or full path to file.
         * @return array|false Return file with these keys: 
         *                        name (file name only), 
         *                        ext (file extension only without dot), 
         *                        parent (any parent path or URL without trailing slash), 
         *                        nameext (just file name with extension).
         *                        Return false if failed.
         */
        public function getFilePart($file)
        {
            if (!is_scalar($file)) {
                return false;
            }

            $output = [];
            $output['parent'] = dirname($file);

            $output['nameext'] = str_replace([$output['parent'], '\\', '/'], '', $file);
            $fileExploded = explode('.', $output['nameext']);

            $output['ext'] = (isset($fileExploded[count($fileExploded) - 1]) ? $fileExploded[count($fileExploded) - 1] : '');
            unset($fileExploded[count($fileExploded) - 1]);
            $output['name'] = implode('.', $fileExploded);

            return $output;
        }// getFilePart


        /**
         * Recursively remove directory.<br>
         * This will delete all files and sub folder in it.
         * 
         * @param string $dir Target folder to delete. Such as /path/to/upload/targetfolder. Everything inside 'targetfolder' even files, sub folders will be deleted.
         * @param string $limited_dir Limited to folder. Such as /path/to/upload. Any path that is match to this or upper than this will be skipped.
         */
        public static function rrmDir($dir, $limited_dir)
        {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($dir . DIRECTORY_SEPARATOR . $object == $limited_dir) {
                        return false;
                    } elseif ($object != '.' && $object != '..') {
                        if (wp_is_writable($dir . DIRECTORY_SEPARATOR . $object)) {
                            if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                                static::rrmDir($dir . DIRECTORY_SEPARATOR . $object, $limited_dir);
                            } else {
                                unlink($dir . DIRECTORY_SEPARATOR . $object);
                            }
                        } else {
                            return false;
                        }
                    }
                }

                if ($dir !== $limited_dir) {
                    rmdir($dir);
                }
            }
        }// rrmDir


        /**
         * Write a file.
         * 
         * @param string $path Path to file.
         * @param mixed $content File content. If this is not scalar then it will be convert to string using `print_r()` function.
         * @param boolean $append Set to true to append the file content. You have to manually add new line if you want to append to new line.
         * @param int $chmod Change mode number. Read more at http://php.net/manual/en/function.chmod.php
         * @return boolean Return true on success, false on failure.
         * @throws \InvalidArgumentException Throw invalid argument error on wrong type.
         */
        public function writeFile($path, $content, $append = true, $chmod = 0666)
        {
            if (!is_string($path)) {
                throw new \InvalidArgumentException(sprintf(__('The %s must be string.', 'rd-downloads'), '$path'));
            }

            if (!is_scalar($content)) {
                $content = trim(print_r($content, true));
            }

            if (!is_bool($append)) {
                $append = true;
            }

            if (!is_int($chmod)) {
                $chmod = 0666;
            }

            if (file_exists($path) && $append === false) {
                // if file exists and append is false.
                return false;
            } else {
                if ($append === true) {
                    $flag = FILE_APPEND;
                } else {
                    $flag = 0;
                }
                file_put_contents($path, $content, $flag);
                chmod($path, $chmod);

                unset($flag);
                return true;
            }
        }// writeFile


    }
}
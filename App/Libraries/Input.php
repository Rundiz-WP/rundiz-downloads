<?php
/**
 * Input library
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\Input')) {
    class Input
    {


        /**
         * Set data value to NULL if it is empty string ('').
         * 
         * @param array $data The data value.
         * @return array Return formatted value.
         */
        public function setNullIfDataValueEmpty(array $data)
        {
            if (is_array($data)) {
                foreach ($data as $name => $value) {
                    if (is_scalar($value) && trim($value) == '') {
                        $data[$name] = null;
                    }
                }// endforeach;
                unset($name, $value);
            }

            return $data;
        }// setNullIfDataValueEmpty


    }
}
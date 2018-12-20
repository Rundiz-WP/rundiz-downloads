<?php
/**
 * Date/Time library.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\DateTime')) {
    class DateTime
    {


        /**
         * Get the date/time from GMT value using WordPress settings.
         * 
         * The result will be translated using `date_i18n()` function.
         * 
         * @param string $datetime_gmt The date/time in GMT 00:00.
         * @param string $format The format of date/time that will be return. Leave empty for using WordPress setting with (+time zone). The format will be trim the space out.
         * @return string Return date/time in local time zone setting in the WordPress.
         * @throws \InvalidArgumentException Throw invalid argument error on wrong type.
         */
        static public function displayDateTime($datetime_gmt, $format = null)
        {
            if (!is_scalar($datetime_gmt)) {
                /* translators: %s: Argument name. */
                throw new \InvalidArgumentException(sprintf(__('The %s must be string.', 'rd-downloads'), '$datetime_gmt'));
            }

            if (!is_scalar($format)) {
                $format = null;
            }
            $format = trim($format);
            if (empty($format) || is_null($format)) {
                $format = get_option('date_format') . ' ' . get_option('time_format') . ' (P)';
            }

            $datetime_gmt_to_local = get_date_from_gmt($datetime_gmt, 'Y-m-d H:i:s');

            return date_i18n($format, strtotime($datetime_gmt_to_local));
        }// displayDateTime


    }
}
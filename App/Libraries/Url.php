<?php
/**
 * URL class.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\Url')) {
    class Url
    {


        /**
         * Get domain name.
         *
         * @param string $url The URL to get domain.
         * @param boolean $noSubdomain Set to true to get domain without sub domains.
         * @return string|null|false Return string if success get domain. Return null if found no domain. Return false if failed to get domain.
         * @throws \InvalidArgumentException Throw invalid argument error on wrong type.
         */
        public function getDomain($url, $noSubdomain = true)
        {
            if (!is_scalar($url)) {
                throw new \InvalidArgumentException(sprintf(__('The %s must be string.', 'rd-downloads'), '$url'));
            }

            $urlParts = parse_url($url);

            if (isset($urlParts['host'])) {
                if ($noSubdomain === true) {
                    $hostExp = explode('.', $urlParts['host']);
                    if (count($hostExp) >= 2) {
                        $domainNoSub = $hostExp[count($hostExp) - 2] . '.' . $hostExp[count($hostExp) - 1];
                    } else {
                        $domainNoSub = null;
                    }
                    unset($hostExp);

                    return $domainNoSub;
                } else {
                    return $urlParts['host'];
                }
            }

            return false;
        }// getDomain


        /**
         * Get download page URL.
         *
         * Example: `[rddownloads id="x"]` short code will convert to `<a href="http://domain.tld/wordpress-install-path/?pagename=rddownloads_page&download_id=x">Download</a>`.<br>
         * The URI /wordpress-install-path/rd-downloads is download page.<br>
         * This page will process the download step.
         *
         * @global array $rd_downloads_options
         * @param integer $download_id The `download_id` from DB.
         * @return string Return generated download page URL.
         */
        public function getDownloadPageUrl($download_id = '')
        {
            $querystring = [];
            $url = false;
            $querystring['pagename'] = 'rddownloads_page';

            if (!empty($download_id)) {
                $querystring['download_id'] = $download_id;
            }

            $url = add_query_arg($querystring, home_url());

            return esc_url($url);
        }// getDownloadPageUrl


        /**
         * Get remote file info.
         *
         * @todo [rd-downloads] change curl to `wp_remote_xxx()` function.
         * @param string $url The URL to get its info.
         * @return array|false Return array with 'data', 'size' keys if success. Return false for failure.
         * @throws \InvalidArgumentException Throw invalid argument error on wrong type.
         */
        public function getRemoteFileInfo($url)
        {
            if (!is_scalar($url)) {
                throw new \InvalidArgumentException(sprintf(__('The %s must be string.', 'rd-downloads'), '$url'));
            }

            $urlParts = parse_url($url);

            if (isset($urlParts['host'])) {
                // if the URL is valid data.
                $output = [];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);

                $data = curl_getinfo($ch);
                if ($data !== false) {
                    $output['data'] = $data;
                    $output['size'] = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                }

                curl_close($ch);
                unset($ch, $data);

                if (isset($output)) {
                    return $output;
                }
            }

            unset($urlParts);

            return false;
        }// getRemoteFileInfo


    }
}
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

                $remoteArgs = [];
                $remoteArgs['redirection'] = 1;
                $remoteArgs['user-agent'] = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
                if (is_string($remoteArgs['user-agent'])) {
                    $remoteArgs['user-agent'] = htmlspecialchars($remoteArgs['user-agent'], ENT_QUOTES);
                }
                $response = wp_remote_get($url, $remoteArgs);
                unset($remoteArgs);

                $headerResult = wp_remote_retrieve_headers($response);
                unset($response);
                Logger::staticDebugLog($headerResult, 'get-remote-file-info-header-result-' . current_time('Ymd-Hi'));

                if ((is_array($headerResult) || is_object($headerResult)) && isset($headerResult['content-length'])) {
                    $output['size'] = $headerResult['content-length'];
                    $output['data'] = (array) $headerResult;
                }
                unset($headerResult);

                if (isset($output)) {
                    return $output;
                }
            }

            unset($urlParts);

            return false;
        }// getRemoteFileInfo


    }
}
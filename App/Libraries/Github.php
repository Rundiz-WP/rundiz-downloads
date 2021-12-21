<?php
/**
 * GitHub class
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\Github')) {
    class Github
    {


        use \RdDownloads\App\AppTrait;


        /**
         * @var array The global plugin's options.
         */
        protected $pluginOptions = [];


        /**
         * @var string The GitHub API v3 URL.
         */
        protected $apiV3Url = 'https://api.github.com';


        /**
         * @var string The GitHub API v4 URL.
         */
        protected $apiV4Url = 'https://api.github.com/graphql';


        /**
         * @var string The OAuth access token name will be use in `user_meta` and `cookie`. Do not change this.
         */
        protected $oauthAccessTokenName = 'rddownloads_githuboauth_accesstoken';


        /**
         * @var array GitHub webhook headers. This will be set via `webhook()` method.
         */
        protected $webhookHeaders = [];


        /**
         * @var string GitHub raw "php://input". This will be set via `webhook()` method.
         */
        protected $webhookPhpInput;


        /**
         * @var string The GitHub webhook secret name will be use in `user_meta`. Do not change this.
         */
        protected $webhookSecretName = 'rddownloads_githubwebhook_secret';


        /**
         * @var array The associate array where key is user_id and its value is the key. This property was set from `validateGetHubWebhook()` method.
         */
        protected $webhookValidSecretKey = [];


        public function __construct()
        {
            $this->getOptions();
            global $rd_downloads_options;
            $this->pluginOptions = $rd_downloads_options;

            // initialize Semver class to be able to use Composer/Semver classes.
            new Semver();
        }// __construct


        /**
         * Add or update GitHub webhook.
         *
         * It will be add if `$hook_id` is empty (not exists). Otherwise it will be update.
         *
         * @see \RdDownloads\App\Libraries\Github::apiV3Request() about `$headers` and `$postData` arguments.
         * @param integer|false $user_id The current user ID. Set to false to get current user ID.
         * @param string|false $hook_id The hook_id get from `apiGetWebhookId()` method. Set to empty if there is no hook ID. Set to false for auto detect.
         * @param string $secretKey The secret key to set into GitHub webhook and use it on call back to this site for auto update.
         * @param string $repoOwner Repository owner.
         * @param string $repoName Repository name.
         * @param array $headers The `wp_remote_request()` headers array.
         * @return array Return value from `apiV3Request()` method.
         * @throws \InvalidArgumentException Throw invalid argument error on wrong type.
         */
        public function apiAddUpdateGitHubWebhook($user_id, $hook_id, $secretKey, $repoOwner, $repoName, array $headers)
        {
            if (!is_scalar($hook_id) && $hook_id !== false && $hook_id !== '') {
                // if $hook_id is not string, not false, not empty.
                /* translators: %s: Argument name. */
                throw new \InvalidArgumentException(sprintf(__('The %s must be string.', 'rd-downloads'), '$hook_id'));
            }

            if ($secretKey == '') {
                // secret key was not set, no need to continue.
                return [];
            }

            if ($user_id === false) {
                $user_id = get_current_user_id();
            }

            if ($hook_id === false) {
                // if $hook_id is set to auto detect.
                $hook_id = $this->apiGetWebhookId($headers, $repoOwner, $repoName);
                if ($hook_id === false) {
                    $hook_id = '';
                }
            }

            $postData = new \stdClass();
            $postData->config = new \stdClass();
            $postData->config->url = $this->getWebhookPayloadUrl();
            $postData->config->content_type = 'json';
            $postData->config->secret = $secretKey;
            $postData->config->insecure_ssl = apply_filters('rddownloads_githubapi_webhookinsecure', '0');
            $postData->events = ['push'];
            $postData->active = true;
            $postData = json_encode($postData);

            if ($hook_id === '') {
                return $this->apiV3Request('/repos/' . $repoOwner . '/' . $repoName . '/hooks', $headers, '', 'POST', $postData);
            } else {
                return $this->apiV3Request('/repos/' . $repoOwner . '/' . $repoName . '/hooks/' . $hook_id, $headers, '', 'PATCH', $postData);
            }
        }// apiAddUpdateGitHubWebhook


        /**
         * Get latest downloads URL and data from selected repository URL.
         *
         * To determine latest update of downloads URL.
         * <pre>- If the selected repository contain "release".
         *     - If contain custom archive file.
         *         Return release URL.
         *     - If not contain custom archive file.
         *         Return auto asset archive file URL.
         * - If the selected repository does not contain "release".
         *     It will return default branch with latest zip URL.</pre>
         *
         * @link https://developer.github.com/v4/explorer/ For demonstrate API request
         * @link https://getcomposer.org/doc/articles/versions.md Version range reference.
         * @param string $url The URL to anywhere in the repository.
         * @param string|array $version_range The version range. See https://getcomposer.org/doc/articles/versions.md for more description.<br>
         *                                                          Set this to version range as documented in Composer to get only matched version range. <br>
         *                                                          Set this to empty string to get latest release.<br>
         *                                                          Set this to empty array to get all releases into array.<br>
         * @param integer|empty $user_id The user ID to get user's access token for get latest data. Set to empty for auto detect.
         * @return array|false Return array if contain latest update by conditions described above, return false for failure.
         *                                  The return array format is:
         *                                  <pre>array(
         *                                      'id' => 'The GitHub archive ID (may not contain this key).',
         *                                      'date' => 'The archive file pushed date (may not contain this key).',
         *                                      'url' => 'The archive file URL.',
         *                                      'size' => 'The archive file size (may not contain this key).',
         *                                      'version' => 'The tag version number (may not contain this key).',
         *                                      'nameWithOwner' => 'The name with owner for this repository. The value exactly is "owner/name" (may not contain this key).',
         *                                  );</pre>
         *                                  If the $version_range is empty array then the return array format is:
         *                                  <pre>array(
         *                                      0 => array(
         *                                          'id' => '...',
         *                                          'date' => '...',
         *                                          '...' => '...',
         *                                      ),
         *                                      1 => array(
         *                                          'id' => '...',
         *                                          'date' => '...',
         *                                          '...' => '...',
         *                                      ),
         *                                  );</pre>
         */
        public function apiGetLatestRepositoryData($url, $version_range = '', $user_id = '')
        {
            $owner_name = $this->getNameWithOwnerFromUrl($url);
            $accessToken = $this->getOAuthAccessToken($user_id);

            if (empty($accessToken)) {
                // if GitHub access token was not set, return original because it cannot check anything.
                $output['url'] = $url;
                if (is_array($owner_name) && isset($owner_name[0]) && isset($owner_name[1])) {
                    $output['nameWithOwner'] = $owner_name[0] . '/' . $owner_name[1];
                }
                unset($accessToken, $owner_name);
                return $output;
            }

            if (is_array($owner_name) && isset($owner_name[0]) && isset($owner_name[1])) {
                $owner = $owner_name[0];
                $name = $owner_name[1];
            } else {
                // cannot detect name/owner from URL. it is not possible to get latest repository data, return false.
                unset($accessToken, $owner_name);
                return false;
            }
            unset($owner_name);

            $headers = [];
            $headers['Authorization'] = 'token ' . $accessToken;
            unset($accessToken);
            $postData = [
                'query' => $this->graphQLLatestRepositoryData($owner, $name),
            ];
            $postData = wp_json_encode($postData);

            $result = $this->apiV4Request($headers, $postData);
            unset($headers, $postData);

            Logger::staticDebugLog($result, 'github-api-request-' . current_time('Ymd-Hi'));

            if (is_array($result) && isset($result['body'])) {
                $result = $result['body'];
            }

            $defaultBranch = [];
            if (isset($result->data->repository->defaultBranchRef->target)) {
                // if contain default branch.
                if (
                    isset($result->data->repository->defaultBranchRef->target->pushedDate) &&
                    isset($result->data->repository->defaultBranchRef->target->zipballUrl)
                ) {
                    $defaultBranch['id'] = $result->data->repository->defaultBranchRef->target->id;
                    $defaultBranch['date'] = $result->data->repository->defaultBranchRef->target->pushedDate;
                    $defaultBranch['url'] = $result->data->repository->defaultBranchRef->target->zipballUrl;
                }
                if (isset($result->data->repository->nameWithOwner)) {
                    $defaultBranch['nameWithOwner'] = $result->data->repository->nameWithOwner;
                }
                if (isset($result->data->repository->url) && isset($result->data->repository->defaultBranchRef->name)) {
                    $defaultBranch['url'] = $result->data->repository->url . '/archive/' . $result->data->repository->defaultBranchRef->name . '.zip';
                }
            }// endif; contain default branch

            $releases = [];
            if (
                isset($result->data->repository->releases->edges) &&
                is_array($result->data->repository->releases->edges)
            ) {
                // if contain releases.
                // setup versions array for re-order its value with Composer/Semver
                $tmpVersions = [];
                $tmpReleases = [];
                foreach ($result->data->repository->releases->edges as $item) {
                    if (isset($item->node->tag->name)) {
                        $tmpVersions[] = $item->node->tag->name;

                        $tmpReleases[$item->node->tag->name] = [];

                        $Semver = new Semver();
                        $tmpReleases[$item->node->tag->name]['version'] = $Semver->removePrefix($item->node->tag->name);// remove prefix "v" for example: v1.0.1 will be 1.0.1
                        unset($Semver);

                        if (isset($item->node->tag->id)) {
                            $tmpReleases[$item->node->tag->name]['id'] = $item->node->tag->id;
                        }
                        if (isset($item->node->tag->target->pushedDate)) {
                            $tmpReleases[$item->node->tag->name]['date'] = $item->node->tag->target->pushedDate;
                        }

                        if (
                            isset($item->node->releaseAssets->edges) &&
                            is_array($item->node->releaseAssets->edges) &&
                            !empty($item->node->releaseAssets->edges)
                        ) {
                            // if contain releases AND custom archive file(s).
                            $fileSizes = [];
                            $maxFileSize = 0;
                            foreach ($item->node->releaseAssets->edges as $itemReleaseAsset) {
                                if (isset($itemReleaseAsset->node->size)) {
                                    $fileSizes[] = $itemReleaseAsset->node->size;
                                }
                            }// endforeach;
                            unset($itemReleaseAsset);

                            if (!empty($fileSizes)) {
                                $maxFileSize = max($fileSizes);
                            }
                            if ($maxFileSize == 0) {
                                $maxFileSize = -1;
                            }
                            unset($fileSizes);

                            if (isset($item->node->url)) {
                                $tmpReleases[$item->node->tag->name]['url'] = $item->node->url;
                            } else {
                                $tmpReleases[$item->node->tag->name]['url'] = $url;
                            }
                            $tmpReleases[$item->node->tag->name]['size'] = $maxFileSize;
                            unset($maxFileSize);
                        } else {
                            // if does not contain custom archive file.
                            if (isset($item->node->tag->target->zipballUrl)) {
                                $tmpReleases[$item->node->tag->name]['url'] = $item->node->tag->target->zipballUrl;
                            } elseif (isset($item->node->url)) {
                                $tmpReleases[$item->node->tag->name]['url'] = $item->node->url;
                            } else {
                                $tmpReleases[$item->node->tag->name]['url'] = $url;
                            }
                        }
                    }
                }// endforeach; releases->edges
                unset($item);

                // re-order the versions
                $tmpVersions = \RdDownloads\Composer\Semver\Semver::rsort($tmpVersions);
                $tmpReleasesReorder = [];
                if (is_array($tmpVersions)) {
                    foreach ($tmpVersions as $tmpVersion) {
                        if (isset($tmpReleases[$tmpVersion])) {
                            $tmpReleasesReorder[$tmpVersion] = $tmpReleases[$tmpVersion];
                        }
                    }// endforeach; $tmpVersions
                    unset($tmpVersion);
                }

                if (empty($tmpReleasesReorder)) {
                    $tmpReleasesReorder = $tmpReleases;
                }
                unset($tmpReleases, $tmpVersions);

                Logger::staticDebugLog($tmpReleasesReorder, 'github-api-request-formatted-and-reordered-array-' . current_time('Ymd-Hi'));

                if (!empty($tmpReleasesReorder) && is_array($tmpReleasesReorder)) {
                    // if not empty $tmpReleasesReorder
                    if ($version_range === '' || is_null($version_range)) {
                        // if version range is empty string, get latest.
                        reset($tmpReleasesReorder);
                        $firstRefsKey = key($tmpReleasesReorder);
                        $releases = $tmpReleasesReorder[$firstRefsKey];
                        if (defined('WP_DEBUG') && WP_DEBUG === true) {
                            $releases['debug_version_range_latest'] = true;
                        }
                        unset($firstRefsKey);
                    } elseif (is_scalar($version_range) && !empty($version_range)) {
                        // if version range is not empty, check using Composer Semver.
                        foreach ($tmpReleasesReorder as $key => $item) {
                            if (is_scalar($key) && \RdDownloads\Composer\Semver\Semver::satisfies($key, $version_range)) {
                                $releases = $tmpReleasesReorder[$key];
                                if (defined('WP_DEBUG') && WP_DEBUG === true) {
                                    $releases['debug_version_range_matchsemver'] = true;
                                }
                                break;
                            }
                        }// endforeach;
                        unset($item, $key);
                    } elseif (is_array($version_range) && empty($version_range)) {
                        // if version range is empty array, get it all.
                        foreach ($tmpReleasesReorder as $key => $item) {
                            $releases[] = $tmpReleasesReorder[$key];
                        }// endforeach;
                        unset($item, $key);
                    }// endif;

                    if (isset($result->data->repository->nameWithOwner)) {
                        $releases['nameWithOwner'] = $result->data->repository->nameWithOwner;
                    }
                }// endif; not empty $tmpReleasesReorder

                unset($tmpReleasesReorder);
            }// endif; contain releases (releases->edges)
            unset($result);

            if (isset($releases) && !empty($releases)) {
                return $releases;
            } elseif (isset($defaultBranch) && !empty($defaultBranch)) {
                return $defaultBranch;
            }

            return false;
        }// apiGetLatestRepositoryData


        /**
         * Make API request to check if there is webhook for this site already on certain repository or not.
         *
         * If there already is then get its hook id.
         *
         * This will be check on github.com website.
         *
         * @link https://developer.github.com/v3/repos/hooks/ repo hook reference.
         * @see \RdDownloads\App\Libraries\Github::apiV3Request() about `$headers` argument.
         * @param array $headers The `wp_remote_request()` headers array.
         * @param string $repoOwner Repository owner.
         * @param string $repoName Repository name.
         * @return string|false Return string if there is webhook id, return false if not found any webhook for this website.
         */
        public function apiGetWebhookId(array $headers, $repoOwner, $repoName)
        {
            $response = $this->apiV3RequestMultiPages('/repos/' . $repoOwner . '/' . $repoName . '/hooks', $headers, '', 'GET');

            if (isset($response['body']) && is_array($response['body'])) {
                foreach ($response['body'] as $hook) {
                    if (isset($hook->config->url) && stripos($hook->config->url, $this->getWebhookPayloadUrl()) !== false) {
                        // if URL in GitHub webhook match this site, this means already have hook.
                        // get the hook id and skip loop.
                        $hook_id = $hook->id;
                        break;
                    }
                }// endforeach;
                unset($hook);
            }
            unset($response);

            if (isset($hook_id)) {
                return $hook_id;
            }
            return false;
        }// apiGetWebhookId


        /**
         * Make API request to remove webhook on github.com
         *
         * @param string|false $hook_id The hook_id get from `apiGetWebhookId()` method. Set to empty if there is no hook ID. Set to false for auto detect.
         * @param string $repoOwner Repository owner.
         * @param string $repoName Repository name.
         * @param array $headers The `wp_remote_request()` headers array.
         * @return array Return value from `apiV3Request()` method.
         */
        public function apiRemoveWebhook($hook_id, $repoOwner, $repoName, array $headers)
        {
            if ($hook_id === false) {
                // if $hook_id is set to auto detect.
                $hook_id = $this->apiGetWebhookId($headers, $repoOwner, $repoName);
                if ($hook_id === false) {
                    return [];
                }
            }

            return $this->apiV3Request('/repos/' . $repoOwner . '/' . $repoName . '/hooks/' . $hook_id, $headers, '', 'DELETE');
        }// apiRemoveWebhook


        /**
         * Get GitHub API v3 headers array.
         *
         * @param string $accessToken The GitHub access token that got via OAuth.
         * @return array Return array of headers that can be use in `wp_remote_xxx()` functions.
         */
        public function apiV3Headers($accessToken)
        {
            $headers = [];
            $headers['Authorization'] = 'token ' . $accessToken;
            $headers['Accept'] = 'application/json';

            return $headers;
        }// apiV3Headers


        /**
         * Make an API v3 request.
         *
         * @link https://developer.github.com/v3/ GitHub API v3 document.
         * @param string $uri The v3 API URI. Always begin with slash.
         * @param array $headers The header key or name must be in array key.
         *                                      For example: The "Authorization: xxx" header must be `$headers['Authorization'] = 'xxx';`.
         * @param string $userPassword Username:Password for basic auth. To use this, the `Authorization` key in `$headers` array must not exists.
         * @param string $method Request method (GET, POST, PATCH, DELETE, ...).
         * @param string $postData The GitHub API v3 query data.
         * @return array Return array with "header" and "body" in array keys. The "body" key return JSON decoded of result from GitHub.
         */
        public function apiV3Request($uri = '', $headers = [], $userPassword = '', $method = 'GET', $postData = '')
        {
            if ($userPassword !== '' && $userPassword !== null) {
                // @link https://johnblackbourn.com/wordpress-http-api-basicauth/ Basic auth for `wp_remote_xxx()`.
                if (!isset($headers['Authorization'])) {
                    $headers['Authorization'] = 'basic ' . base64_encode($userPassword);
                }
            }
            $remoteArgs = [];
            $remoteArgs['method'] = $method;
            $remoteArgs['headers'] = $headers;
            $remoteArgs['body'] = $postData;
            $remoteArgs['redirection'] = 0;
            $remoteArgs['user-agent'] = $this->setUserAgent();

            $result = wp_remote_request($this->apiV3Url . $uri, $remoteArgs);
            Logger::staticDebugLog('Request URL: ' . $this->apiV3Url . $uri, 'github-apiv3-rawresponse-' . current_time('Ymd-Hi'));
            Logger::staticDebugLog('Arguments: ' . var_export($remoteArgs, true), 'github-apiv3-rawresponse-' . current_time('Ymd-Hi'));
            Logger::staticDebugLog($result, 'github-apiv3-rawresponse-' . current_time('Ymd-Hi'));
            Logger::staticDebugLog('END log ' . str_repeat('-', 60), 'github-apiv3-rawresponse-' . current_time('Ymd-Hi'));
            unset($remoteArgs);

            $output = [];
            $output['header'] = '';
            $output['body'] = new \stdClass();

            if (is_array($result)) {
                if (isset($result['body'])) {
                    $output['body'] = json_decode($result['body']);
                }
                if (isset($result['headers'])) {
                    $output['header'] = $result['headers'];
                }
                if (isset($result['response']['code'])) {
                    $output['header']['status-int'] = $result['response']['code'];
                }
            }

            unset($result);
            return $output;
        }// apiV3Request


        /**
         * Make API v3 request multiple pages at once.
         *
         * This function work the same way as `apiV3Request()` method but request multiple pages to get all values at once.
         *
         * @see apiV3Request() See `apiV3Request()` method for more details.
         * @param string $uri The v3 API URI. Always begin with slash.
         * @param array $headers The header key or name must be in array key.
         *                                      For example: The "Authorization: xxx" header must be `$headers['Authorization'] = 'xxx';`.
         * @param string $userPassword Username:Password for basic auth. To use this, the `Authorization` key in `$headers` array must not exists.
         * @param string $method Request method (GET, POST, PATCH, DELETE, ...).
         * @param string $postData The GitHub API v3 query data.
         * @return array Return array with "header" and "body" in array keys. The "body" key return JSON decoded of result from GitHub.
         */
        public function apiV3RequestMultiPages($uri = '', $headers = [], $userPassword = '', $method = 'GET', $postData = '')
        {
            $output = [];
            $i = 1;
            $end = false;
            $perPage = 100;

            do {
                $paginateUri = $uri . '?page=' . $i . '&per_page=' . $perPage;
                $response = $this->apiV3Request($paginateUri, $headers, $userPassword, $method, $postData);
                unset($paginateUri);

                if (!isset($output['header']) && isset($response['header'])) {
                    $output['header'] = $response['header'];
                }

                if (!isset($output['body'])) {
                    $output['body'] = [];
                }

                if (isset($response['body']) && is_array($response['body']) && !empty($response['body'])) {
                    $output['body'] = array_merge($output['body'], $response['body']);
                } else {
                    $end = true;
                }

                $i++;
                unset($response);

                if ($i > 100) {
                    $end = true;
                }
            }// end do;
            while($end == false);

            unset($end, $i, $perPage);

            return $output;
        }// apiV3RequestMultiPages


        /**
         * Make an API v4 request.
         *
         * @link https://developer.github.com/v4/guides/ GitHub API v4 document.
         * @param array $headers The header key or name must be in array key.
         *                                      For example: The "Authorization: xxx" header must be `$headers['Authorization'] = 'xxx';`.
         *                                      For what header GitHub will be accepted, please see https://developer.github.com/v4/guides/ for more info.
         * @param array|string $postData Set data to array or JSON encoded of data array.
         *                                      See https://developer.github.com/v4/guides/using-the-explorer/ for more info
         *                                      or https://developer.github.com/v4/explorer/ for demonstrate API request.
         * @return array Return array with "header" and "body" in array keys. The "body" key return JSON decoded of result from GitHub.
         */
        public function apiV4Request(array $headers, $postData)
        {
            if (!is_string($postData)) {
                $postData = wp_json_encode($postData);
            }

            $remoteArgs = [];
            $remoteArgs['headers'] = $headers;
            $remoteArgs['body'] = $postData;
            $remoteArgs['redirection'] = 0;
            $remoteArgs['user-agent'] = $this->setUserAgent();

            $result = wp_remote_post($this->apiV4Url, $remoteArgs);
            unset($remoteArgs);

            $output = [];
            $output['header'] = '';
            $output['body'] = new \stdClass();

            if (is_array($result)) {
                if (isset($result['body'])) {
                    $output['body'] = json_decode($result['body']);
                }
                if (isset($result['headers'])) {
                    $output['header'] = $result['headers'];
                }
                if (isset($result['response']['code'])) {
                    $output['header']['status-int'] = $result['response']['code'];
                }
            }

            unset($result);
            return $output;
        }// apiV4Request


        /**
         * Generate webhook secret key.
         *
         * @param integer $user_id
         * @return string
         */
        public function generateWebhookSecretKey($user_id = '')
        {
            if (empty($user_id)) {
                $user_id = get_current_user_id();
            }

            return $user_id . '_' . wp_generate_password(20, false, false);
        }// generateWebhookSecretKey


        /**
         * Get the GitHub repository name with owner from URL.
         *
         * @param string $url The GitHub repository URL, for example: https://github.com/myuser/myrepo/archive/master.zip .
         * @return array|false From the URL above it will return owner as first array, name of repository as second array.
         *                                  Example: array('myuser', 'myrepo'). Return false if failed to get name with owner.
         */
        public function getNameWithOwnerFromUrl($url)
        {
            $urlParts = parse_url($url);
            if (isset($urlParts['path'])) {
                $pathExp = explode('/', ltrim($urlParts['path'], '/'));
                if (is_array($pathExp) && count($pathExp) >= 2) {
                    $output = [];
                    $output[0] = $pathExp[0];
                    $output[1] = $pathExp[1];
                }
                unset($pathExp);
            }
            unset($urlParts);

            if (isset($output)) {
                return $output;
            }

            return false;
        }// getNameWithOwnerFromUrl


        /**
         * Get access token from user meta (DB).
         *
         * The access token have got when user connected with GitHub OAuth.
         *
         * @param integer $user_id The user ID. Leave blank for get current user ID.
         * @return string|false Return user access token. Return false if not found.
         */
        public function getOAuthAccessToken($user_id = '')
        {
            if (empty($user_id) || $user_id <= 0) {
                $user_id = get_current_user_id();
            }

            $accessToken = get_user_meta($user_id, $this->oauthAccessTokenName, true);
            if ($accessToken === '' || $accessToken === null || $accessToken === false) {
                return false;
            }
            return $accessToken;
        }// getOAuthAccessToken


        /**
         * Get OAuth access token name that used in `user_meta` and `cookie`.
         *
         * @return string Return the name to use in `user_meta` and `cookie`.
         */
        public function getOAuthAccessTokenName()
        {
            return $this->oauthAccessTokenName;
        }// getOAuthAccessTokenName


        /**
         * Get GitHub webhook payload URL (for use auto update).
         *
         * @return string Return the accept auto update URL.
         */
        public function getWebhookPayloadUrl()
        {
            return add_query_arg(['pagename' => 'rddownloads_github_autoupdate'], home_url());
        }// getWebhookPayloadUrl


        /**
         * Get webhook secret key (value).
         *
         * The secret key will be use on GitHub auto update (push events).
         *
         * @param integer $user_id
         * @return string|false Return user's secret key. Return false if not found.
         */
        public function getWebhookSecretKey($user_id)
        {
            if (empty($user_id) || $user_id <= 0) {
                $user_id = get_current_user_id();
            }

            $secretKey = get_user_meta($user_id, $this->webhookSecretName, true);
            if ($secretKey === '' || $secretKey === null || $secretKey === false) {
                return false;
            }
            return $secretKey;
        }// getWebhookSecretKey


        /**
         * Get GitHub webhook secret name that will be use in `user_meta`.
         *
         * @return string Return the name to use in `user_meta`.
         */
        public function getWebhookSecretName()
        {
            return $this->webhookSecretName;
        }// getWebhookSecretName


        /**
         * Get valid secret key after called `validateGitHubWebhook()` method.
         *
         * @return array Return the associate array where key is user_id and its value is the key.
         */
        public function getWebhookValidSecretKey()
        {
            return $this->webhookValidSecretKey;
        }// getWebhookValidSecretKey


        /**
         * GitHub GraphQL for all repositories (users and their organizations).
         *
         * @return string Return graphQL string with replacable content.<br>
         *                          Replacable content: %after%, %before%
         */
        public function graphQLAllRepositories()
        {
            return 'query {
  viewer {
    login
    name
    repositories(%after%%before%first: 100, isFork: false, orderBy: {field: PUSHED_AT, direction: DESC}, ownerAffiliations: [OWNER, COLLABORATOR, ORGANIZATION_MEMBER]) {
      pageInfo {
        startCursor
        hasPreviousPage
        hasNextPage
        endCursor
      }
      totalCount
      totalDiskUsage
      edges {
        node {
          isArchived
          name
          owner {
            login
          }
          nameWithOwner
          url
        }
      }
    }
  }
                }';
        }// graphQLAllRepositories


        /**
         * GitHub GraphQL for latest repository data.
         *
         * @param string $owner The repository owner.
         * @param string $name The repository name.
         * @return string Return GraphQL string
         */
        protected function graphQLLatestRepositoryData($owner, $name)
        {
            return 'query {
  repository(owner: "' . $owner . '", name: "' . $name . '") {
    id
    url
    nameWithOwner
    releases(last: 100) {
      totalCount
      edges {
        node {
          name
          tag {
            id
            name
            target {
              ... on Commit {
                pushedDate
                zipballUrl
              }
            }
          }
          releaseAssets(last: 100) {
            totalCount
            edges {
              node {
                id
                updatedAt
                downloadUrl
                size
                downloadCount
                release {
                  tagName
                  publishedAt
                }
              }
            }
          }
          url
        }
      }
    }
    defaultBranchRef {
      name
      target {
        ... on Commit {
          id
          pushedDate
          zipballUrl
        }
      }
    }
  }
              }';
        }// graphQLLatestRepositoryData


        /**
         * Check that if Client ID and Client Secret was set in global plugin settings page.
         *
         * @return boolean
         */
        protected function isClientIdSecretWasSet()
        {
            if (
                isset($this->pluginOptions['rdd_github_client_id']) &&
                isset($this->pluginOptions['rdd_github_client_secret']) &&
                !empty($this->pluginOptions['rdd_github_client_id']) &&
                !empty($this->pluginOptions['rdd_github_client_secret'])
            ) {
                return true;
            }

            return false;
        }// isClientIdSecretWasSet


        /**
         * Check if OAuth disconnected.
         * 
         * @return bool
         */
        public function isOAuthDisconnected()
        {
            if (isset($_COOKIE[$this->oauthAccessTokenName])) {
                return false;
            }
            return true;
        }// isOAuthDisconnected


        /**
         * Check that if global setting is set to auto update or not.
         *
         * @global \wpdb $wpdb
         * @return boolean Return true if yes, return false for otherwise.
         */
        public function isSettingToAutoUpdate()
        {

            if (
                !isset($this->pluginOptions['rdd_github_auto_update']) ||
                (
                    isset($this->pluginOptions['rdd_github_auto_update']) &&
                    $this->pluginOptions['rdd_github_auto_update'] == ''
                )
            ) {
                // if github setting is not to auto update.
                return false;
            }

            global $wpdb;
            $sql = 'SELECT * FROM `' . $wpdb->usermeta . '` WHERE `meta_key` = %s AND `meta_value` != \'\'';
            $result = $wpdb->get_var($wpdb->prepare($sql, [$this->getWebhookSecretName()]));
            unset($sql);

            if (empty($result)) {
                return false;
            }
            unset($result);

            return true;
        }// isSettingToAutoUpdate


        /**
         * Disconnect from GitHub OAuth.
         *
         * Clear the cookie and set user meta where access token was stored to empty.
         *
         * @param integer|empty $user_id
         */
        public function oauthDisconnect($user_id = '')
        {
            if (empty($user_id) || $user_id <= 0) {
                $user_id = get_current_user_id();
            }

            \RdDownloads\App\Libraries\Cookies::deleteCookie($this->oauthAccessTokenName);
            unset($_COOKIE[$this->oauthAccessTokenName]);

            // don't remove access token data.
            // leave this to make auto update works, the auto update also required this thing.
            //update_user_meta($user_id, $this->oauthAccessTokenName, '');// don't remove this line to remind myself.
        }// oauthDisconnect


        /**
         * Get OAuth access token from GitHub (github.com) (step 2).
         *
         * Use the code receive from GitHub to exchange with access token from GitHub.
         *
         * @param string $code The code receive from authorized at GitHub.
         * @param string $redirect_uri The "redirect_uri" value.
         * @param string $state The "state" value.
         * @return string|object Return string with access token if success, return object if contain error, return empty string if config was not set.
         * @throws \Exception
         */
        public function oauthGetAccessToken($code, $redirect_uri, $state_nonce)
        {
            if ($this->isClientIdSecretWasSet() === true) {
                $postBody = [];
                $postBody['client_id'] = $this->pluginOptions['rdd_github_client_id'];
                $postBody['client_secret'] = $this->pluginOptions['rdd_github_client_secret'];
                $postBody['code'] = $code;
                $postBody['redirect_uri'] = $redirect_uri;
                $postBody['state'] = $state_nonce;

                $remoteArgs = [];
                $remoteArgs['headers']['Accept'] = 'application/json';
                $remoteArgs['body'] = $postBody;
                $remoteArgs['redirection'] = 0;
                $result = wp_remote_post('https://github.com/login/oauth/access_token', $remoteArgs);
                unset($postBody, $remoteArgs);

                if (is_array($result) && isset($result['body'])) {
                    $body = json_decode($result['body']);
                    if (
                        is_object($body) &&
                        isset($body->access_token) &&
                        isset($body->scope) && // scope can be value1,value2 without space. for example "admin:repo_hook,gist"
                        stripos($body->scope, 'admin:repo_hook') !== false && // make sure that required scope is met.
                        stripos($body->scope, 'read:org') !== false
                    ) {
                        return $body->access_token;
                    } elseif (
                        is_object($body) &&
                        isset($body->error) &&
                        isset($body->error_description)
                    ) {
                        return $body;
                    }
                } elseif (is_wp_error($result)) {
                    throw new \Exception($result->get_error_message());
                }
            }

            return '';
        }// oauthGetAccessToken


        /**
         * Get OAuth link to login (step 1).
         *
         * @link https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/ Reference.
         * @param string $redirect_uri The "redirect_uri" value.
         * @param string $state The "state" value.
         * @return string Return generated link if success, return empty string if config was not set.
         * @throws \Exception
         */
        public function oauthGetLink($redirect_uri, $state_nonce)
        {
            if ($this->isClientIdSecretWasSet() === true) {
                $getBody = [];
                $getBody['client_id'] = $this->pluginOptions['rdd_github_client_id'];
                $getBody['redirect_uri'] = $redirect_uri;
                $getBody['scope'] = 'admin:repo_hook read:org';// space separate scopes. example: 'admin:repo_hook read:org'
                $getBody['state'] = $state_nonce;
                $url = 'https://github.com/login/oauth/authorize?' . http_build_query($getBody);
                unset($getBody);

                $remoteArgs = [];
                $remoteArgs['redirection'] = 0;
                $result = wp_remote_get($url, $remoteArgs);
                unset($remoteArgs);

                if (is_array($result) && isset($result['headers']['location'])) {
                    return $result['headers']['location'];
                } elseif (is_wp_error($result)) {
                    throw new \Exception($result->get_error_message());
                }
            }

            return '';
        }// oauthGetLink


        /**
         * Setup User agent for the remote request.
         *
         * @return string Return user agent string.
         */
        protected function setUserAgent()
        {
            if (function_exists('get_home_url')) {
                $ua = get_home_url();
            }

            if (!isset($ua) || (isset($ua) && !is_string($ua))) {
                $ua = (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '');
                if (empty($ua)) {
                    $ua = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknow server address.');
                }
            }

            return 'Rundiz Downloads on ' . $ua;
        }// setUserAgent


        /**
         * Validate GitHub webhook.
         *
         * Check PHP input with secret key that come with headers must be matched.
         *
         * You have to call `webhook()` method before calling this.
         *
         * @global \wpdb $wpdb
         * @return boolean Return true on success, return false on failure.
         */
        public function validateGitHubWebhook()
        {
            $headers = array_change_key_case($this->webhookHeaders);
            if (!isset($headers['x-hub-signature']) || (isset($headers['x-hub-signature']) && !is_scalar($headers['x-hub-signature']))) {
                // if no signature for check.
                return false;
            }

            $explodeSignature = explode('=', $headers['x-hub-signature']);
            if (!isset($explodeSignature[0]) || (isset($explodeSignature[0]) && !is_scalar($explodeSignature[0]))) {
                // if invalid signature (sha1=xxxx was not found).
                unset($explodeSignature);
                return false;
            }
            $hashAlgo = $explodeSignature[0];
            unset($explodeSignature);

            global $wpdb;

            // get the repository name (owner/name) to check that how many users own this.
            // then get the secret key from those users to validate webhook.
            $payloadObject = json_decode($this->webhookPhpInput);
            if (!isset($payloadObject->repository->full_name)) {
                // if not found repository full name (owner/name).
                unset($hashAlgo, $payloadObject);
                return false;
            } else {
                $sql = 'SELECT `user_id`, `download_github_name` FROM `' . $wpdb->prefix . 'rd_downloads` WHERE `download_github_name` = %s GROUP BY `user_id`';
                $downloadItems = $wpdb->get_results($wpdb->prepare($sql, [$payloadObject->repository->full_name]));
                unset($sql);

                if (is_array($downloadItems) && !empty($downloadItems)) {
                    // if found.
                    $user_ids = [];
                    foreach ($downloadItems as $row) {
                        $user_ids[] = $row->user_id;
                    }// endforeach;
                    unset($row);
                } else {
                    // if not found.
                    unset($downloadItems, $hashAlgo, $payloadObject);
                    return false;
                }
                unset($downloadItems);
            }
            unset($payloadObject);

            if (isset($user_ids) && is_array($user_ids)) {
                // if there is at lease 1 user ID or more.
                // get secret keys from users.
                // @link https://stackoverflow.com/a/10634225/128761 WHERE IN prepare.
                $sql = 'SELECT * FROM `' . $wpdb->usermeta . '` WHERE `meta_key` = %s AND `user_id` IN (' . implode(', ', array_fill(0, count($user_ids), '%d')) . ')';
                $data = [];
                $data[] = $this->getWebhookSecretName();
                $data = array_merge($data, $user_ids);
                $userMetaResults = $wpdb->get_results($wpdb->prepare($sql, $data));
                unset($data, $sql);
            }
            unset($user_ids);

            if (!isset($userMetaResults) || empty($userMetaResults)) {
                // if not found any secret key.
                return false;
            } else {
                // if found at lease one secret key or more.
                // check that which one is match this signature.
                foreach ($userMetaResults as $row) {
                    $buildSignature = $hashAlgo . '=' . hash_hmac($hashAlgo, $this->webhookPhpInput, $row->meta_value);
                    if ($buildSignature === $headers['x-hub-signature']) {
                        $this->webhookValidSecretKey = [];
                        $this->webhookValidSecretKey[$row->user_id] = $row->meta_value;
                        unset($buildSignature, $hashAlgo, $userMetaResults);
                        return true;
                    }
                }// endforeach;
                unset($hashAlgo, $row, $userMetaResults);
            }

            return false;
        }// validateGitHubWebhook


        /**
         * Set webhook data.
         *
         * The `webhook` prefix methods is for accept request from GitHub to work with auto update.
         *
         * @param array $headers The header array. This is basically get it from `getallheaders()` PHP function.
         * @param string $phpinput The input from `php://input` that get via `file_get_contents()` PHP function.
         * @throws \InvalidArgumentException Throw invalid argument error on wrong type.
         */
        public function webhook(array $headers, $phpinput)
        {
            if (!is_scalar($phpinput)) {
                /* translators: %s: Argument name. */
                throw new \InvalidArgumentException(sprintf(__('The %s must be string.', 'rd-downloads'), '$phpinput'));
            }

            $this->webhookHeaders = $headers;
            $this->webhookPhpInput = $phpinput;
        }// webhook


        /**
         * Get event from header.
         *
         * You have to call `webhook()` method before calling this.
         *
         * @return string Return lower case of event from header such as ping, push, or empty string.
         */
        public function webhookGetHeaderEvent()
        {
            $headers = array_change_key_case($this->webhookHeaders);
            if (isset($headers['x-github-event'])) {
                return strtolower($headers['x-github-event']);
            }
            unset($headers);
            return '';
        }// webhookGetHeaderEvent


        /**
         * Is this a commit event?
         *
         * You have to call `webhook()` method before calling this.
         *
         * @return boolean Return true if yes, return false if not.
         */
        public function webhookIsCommit()
        {
            $payloadObject = json_decode($this->webhookPhpInput);

            if (
                // contain ref.
                isset($payloadObject->ref) &&
                // ref must contain heads.
                stripos($payloadObject->ref, 'refs/heads/') !== false &&
                // not created (this can be happens on create new branch).
                isset($payloadObject->created) &&
                $payloadObject->created === false &&
                // not deleted (this can happens on delete branch).
                isset($payloadObject->deleted) &&
                $payloadObject->deleted === false &&
                // must have commit or head_commit data.
                (
                    (
                        isset($payloadObject->commits) &&
                        is_array($payloadObject->commits) &&
                        !empty($payloadObject->commits)
                    ) ||
                    (
                        isset($payloadObject->head_commit) &&
                        is_array($payloadObject->head_commit) &&
                        !empty($payloadObject->head_commit)
                    )
                )
            ) {
                return true;
            }

            return false;
        }// webhookIsCommit


        /**
         * Is this a tag event?
         *
         * You have to call `webhook()` method before calling this.
         *
         * @param string $action The action to check. Value can be "created", "deleted" without double quote and lower case.
         * @return boolean Return true if action to check is true, false for otherwise.
         */
        public function webhookIsTagging($action = 'created')
        {
            $payloadObject = json_decode($this->webhookPhpInput);

            if (
                // contain ref.
                isset($payloadObject->ref) &&
                // ref must contain tags.
                stripos($payloadObject->ref, 'refs/tags/') !== false
            ) {
                if (
                    // checking for created.
                    strtolower($action) === 'created' &&
                    // is created.
                    isset($payloadObject->created) &&
                    $payloadObject->created === true
                ) {
                    // if action to check is created and it is created.
                    return true;
                } elseif (
                    // checking for deleted.
                    strtolower($action) === 'deleted' &&
                    // is deleted.
                    isset($payloadObject->deleted) &&
                    $payloadObject->deleted === true
                ) {
                    // if action to check is deleted and it is deleted.
                    return true;
                }
            }

            return false;
        }// webhookIsTagging


        /**
         * Check for configuration on GitHub webhook pinging (content-type is application/json, event is push).
         *
         * You have to call `webhook()` method before calling this.
         *
         * @return true|false|null Return true on success, false on failure, null if it is not pinging.
         * @throws \InvalidArgumentException Throw the error on mismatched type.
         */
        public function webhookPingCheckConfig()
        {
            $headers = array_change_key_case($this->webhookHeaders);
            if (isset($headers['x-github-event']) && strtolower($headers['x-github-event']) === 'ping') {
                if (isset($headers['content-type']) && trim(strtolower($headers['content-type'])) === 'application/json') {
                    $contentTypeOkay = true;
                }
            } else {
                // if this is not pinging, no need to check anymore.
                return null;
            }

            if (isset($contentTypeOkay) && $contentTypeOkay === true) {
                // if content type was checked and ok!
                $payloadObject = json_decode($this->webhookPhpInput);

                if (isset($payloadObject->zen)) {
                    // if really pinging.
                    if (isset($payloadObject->hook->events)) {
                        // if contain events and config for check.
                        if (is_array($payloadObject->hook->events)) {
                            foreach ($payloadObject->hook->events as $configEvent) {
                                if (is_scalar($configEvent) && strtolower($configEvent) === 'push') {
                                    $pingOkay = true;
                                    break;
                                }
                            }// endforeach;
                            unset($configEvent);
                        }

                        if (isset($pingOkay) && $pingOkay === true) {
                            unset($contentTypeOkay, $payloadObject, $pingOkay);
                            return true;
                        }
                    }// endif contain events and config for check.
                }// endif; zen (really pinging)
                unset($contentTypeOkay, $payloadObject, $pingOkay);
            }// endif; content-type was ok!

            return false;
        }// webhookPingCheckConfig


    }
}
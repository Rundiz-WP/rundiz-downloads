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
         * @var string The GitHub API v4 URL.
         */
        protected $apiUrl = 'https://api.github.com/graphql';


        /**
         * @var array GitHub webhook headers. This will be set via `webhook()` method.
         */
        protected $webhookHeaders = [];


        /**
         * @var string GitHub raw "php://input". This will be set via `webhook()` method.
         */
        protected $webhookPhpInput;


        public function __construct()
        {
            $this->getOptions();
            global $rd_downloads_options;
            $this->pluginOptions = $rd_downloads_options;
        }// __construct


        /**
         * Make an API request.
         * 
         * @link https://developer.github.com/v4/guides/ GitHub API v4 document.
         * @param array $headers See https://developer.github.com/v4/guides/ for more info.
         * @param array|string $postData Set data to array or JSON encoded of data array. See https://developer.github.com/v4/guides/using-the-explorer/ for more info or https://developer.github.com/v4/explorer/ for demonstrate API request.
         * @return object Return JSON decoded of result from GitHub.
         */
        public function apiRequest(array $headers, $postData)
        {
            if (!is_string($postData)) {
                $postData = json_encode($postData);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->setUserAgent());
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $output = curl_exec($ch);
            curl_close($ch);
            unset($ch);
            $output = json_decode(trim($output));

            return $output;
        }// apiRequest


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
         * @param string $url The URL to anywhere in the repository.
         * @return array|false Return array if contain latest update by conditions described above, return false for failure.
         *                                  The return array format is: 
         *                                  <pre>array(
         *                                      'id' => 'The GitHub archive ID (may not contain this key).',
         *                                      'date' => 'The archive file pushed date (may not contain this key).',
         *                                      'url' => 'The archive file URL.',
         *                                      'size' => 'The archive file size (may not contain this key).',
         *                                      'nameWithOwner' => 'The name with owner for this repository. The value exactly is "owner/name" (may not contain this key).',
         *                                  );</pre>
         */
        public function getLatestRepositoryData($url)
        {
            $owner_name = $this->getNameWithOwnerFromUrl($url);

            if (
                !isset($this->pluginOptions['rdd_github_token']) || 
                (isset($this->pluginOptions['rdd_github_token']) && empty(trim($this->pluginOptions['rdd_github_token'])))
            ) {
                // if GitHub token was not set, return original because it cannot check anything.
                $output['url'] = $url;
                if (is_array($owner_name) && isset($owner_name[0]) && isset($owner_name[1])) {
                    $output['nameWithOwner'] = $owner_name[0] . '/' . $owner_name[1];
                }
                unset($owner_name);
                return $output;
            }

            if (is_array($owner_name) && isset($owner_name[0]) && isset($owner_name[1])) {
                $owner = $owner_name[0];
                $name = $owner_name[1];
            } else {
                // cannot detect name/owner from URL. it is not possible to get latest repository data, return false.
                return false;
            }
            unset($owner_name);

            $headers = [
                'Authorization: bearer ' . (isset($this->pluginOptions['rdd_github_token']) ? $this->pluginOptions['rdd_github_token'] : ''),
            ];
            $postData = [
                'query' => 'query {
                    repository(owner: "' . $owner . '", name: "' . $name . '") {
                      id
                      url
                      nameWithOwner
                      releases(last: 1) {
                        totalCount
                        edges {
                          node {
                            name
                            tag {
                              name
                              target {
                                ... on Commit {
                                  id
                                  pushedDate
                                  zipballUrl
                                }
                              }
                            }
                            releaseAssets(last:100) {
                              totalCount
                              edges {
                                node {
                                  id
                                  updatedAt
                                  downloadUrl
                                  size
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
                  }'
            ];
            $postData = wp_json_encode($postData);

            $result = $this->apiRequest($headers, $postData);
            unset($headers, $postData);

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
            if (isset($result->data->repository->releases->edges[0]->node)) {
                // if contain "release".
                $releaseNode = $result->data->repository->releases->edges[0]->node;

                if (
                    isset($releaseNode->releaseAssets->edges) && 
                    is_array($releaseNode->releaseAssets->edges) &&
                    !empty($releaseNode->releaseAssets->edges)
                ) {
                    // if contain release AND custom archive file(s).
                    $totalCustomArchiveSize = 0;
                    foreach ($releaseNode->releaseAssets->edges as $item) {
                        if (isset($item->node->size)) {
                            $totalCustomArchiveSize = ($totalCustomArchiveSize + $item->node->size);
                        }
                    }// endforeach;
                    unset($item);
                    if ($totalCustomArchiveSize == 0) {
                        $totalCustomArchiveSize = -1;
                    }
                    if (isset($releaseNode->url)) {
                        $releases['url'] = $releaseNode->url;
                    } else {
                        $releases['url'] = $url;
                    }
                    $releases['size'] = $totalCustomArchiveSize;
                    unset($totalCustomArchiveSize);
                } else {
                    // if does not contain custom archive file.
                    if (
                        isset($releaseNode->tag->target->pushedDate) &&
                        isset($releaseNode->tag->target->zipballUrl)
                    ) {
                        $releases['id'] = $releaseNode->tag->target->id;
                        $releases['date'] = $releaseNode->tag->target->pushedDate;
                        $releases['url'] = $releaseNode->tag->target->zipballUrl;
                    }
                }
                unset($releaseNode);

                if (isset($result->data->repository->nameWithOwner)) {
                    $releases['nameWithOwner'] = $result->data->repository->nameWithOwner;
                }
            }// endif; contain "release"
            unset($result);

            if (isset($releases) && !empty($releases)) {
                return $releases;
            } elseif (isset($defaultBranch) && !empty($defaultBranch)) {
                return $defaultBranch;
            }

            return false;
        }// getLatestRepositoryData


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
         * Get GitHub API URL.
         * 
         * @return string Return the API URL.
         */
        public function getApiUrl()
        {
            return $this->apiUrl;
        }// getApiUrl


        /**
         * Check that if global setting is set to auto update or not.
         * 
         * @return boolean Return true if yes, return false for otherwise.
         */
        public function isSettingToAutoUpdate()
        {
            if (
                !isset($this->pluginOptions['rdd_github_secret']) || 
                (
                    isset($this->pluginOptions['rdd_github_secret']) && 
                    empty($this->pluginOptions['rdd_github_secret'])
                )
            ) {
                // if github secret was not set.
                return false;
            }

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

            return true;
        }// isSettingToAutoUpdate


        /**
         * Setup User agent for the CURL request.
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
         * @return boolean Return true on success, return false on failure.
         */
        public function validateGitHubWebhook()
        {
            if (!isset($this->webhookHeaders['X-Hub-Signature']) || (isset($this->webhookHeaders['X-Hub-Signature']) && !is_scalar($this->webhookHeaders['X-Hub-Signature']))) {
                return false;
            }

            $explodeSignature = explode('=', $this->webhookHeaders['X-Hub-Signature']);
            if (!isset($explodeSignature[0]) || (isset($explodeSignature[0]) && !is_scalar($explodeSignature[0]))) {
                unset($explodeSignature);
                return false;
            }
            $hashAlgo = $explodeSignature[0];
            unset($explodeSignature);

            $buildSignature = $hashAlgo . '=' . hash_hmac($hashAlgo, $this->webhookPhpInput, $this->pluginOptions['rdd_github_secret']);
            unset($hashAlgo);

            return $buildSignature === $this->webhookHeaders['X-Hub-Signature'];
        }// validateGitHubWebhook


        /**
         * Set webhook data.
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
         * Check for configuration on GitHub webhook pinging.
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
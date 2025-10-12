/**
 * GitHub connect (OAuth) JS.
 */


/**
 * GitHub connect (OAuth) JS class.
 */
class RdDownloadsGitHubOAuth {


    /**
     * Class constructor of GitHub OAuth.
     * 
     * @since 1.0.14
     */
    constructor() {
        this.#listenClickShowHideSecret();
        this.#listenClickRegenerateSecret();
        this.#listenClickCheckRepoWebhook();
        this.#listenClickForceSyncSecret();
    }// constructor


    /**
     * Enable or disable form elements such as buttons.
     * 
     * @since 1.0.14 Moved from `enableDisableButtons()` with new code that did not use jQuery.
     * @param {boolean} enable Set to `true` to enable elements, `false` to disable them. Default is `true`.
     */
    #enableButtons(enable = true) {
        if (typeof(enable) !== 'boolean') {
            enable = true;
        }

        const thisPage = document.querySelector('.rd-downloads-page-githuboauth');
        thisPage.querySelectorAll('button, input, select')?.forEach((item) => {
            if (true === enable) {
                item.disabled = false;
            } else if (false === enable) {
                item.disabled = true;
            }
        });
    }// #enableButtons


    /**
     * Listen click and check repository webhook that is it already added or not.
     * 
     * This method was called from `constructor()`.
     * 
     * @since 1.0.14 Moved from `checkRepoWebhook()` with new code that did not use jQuery.
     */
    #listenClickCheckRepoWebhook() {
        const checkButton = document.querySelector('.rddownloads_githubrepo_webhook_check');
        if (!checkButton) {
            console.error('[rd-downloads]: Check repo web hook button is not exists.');
            return;
        }

        document.addEventListener('click', (event) => {
            let thisTarget;
            if (event.target.closest('.rddownloads_githubrepo_webhook_check')) {
                thisTarget = event.target.closest('.rddownloads_githubrepo_webhook_check');
                event.preventDefault();
            } else {
                return;
            }

            const thisTr = thisTarget.closest('tr');
            const statusIcon = thisTarget.querySelector('.rddownloads_icon-webhook-status');
            statusIcon.className = '';
            statusIcon.classList.add('rddownloads_icon-webhook-status', 'fas', 'fa-solid', 'fa-spinner', 'fa-pulse', 'fontawesome-icon');
            const formResultPlaceholder = document.querySelector('.rd-downloads-form-result-placeholder');
            formResultPlaceholder.innerHTML = '';

            const formData = new FormData();
            formData.set('security', RdDownloads.nonce);
            formData.set('action', 'RdDownloadsCheckGitHubWebhook');
            formData.set('namewithowner', thisTr.dataset.namewithowner);
            let queryString = '?';
            queryString += new URLSearchParams(formData).toString();

            fetch(ajaxurl + queryString, {
                'method': 'GET',
                'headers': {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
            })
            .then(async (rawResponse) => {
                const contentType = rawResponse.headers.get('content-type');
                let response;
                if (contentType && contentType.includes('application/json')) {
                    response = await rawResponse.json();
                } else {
                    let message = await rawResponse.text();
                    if ('' === message) {
                        if (400 === rawResponse.status) {
                            message = 'Bad Request';
                        }
                    }
                    console.warn('[rd-downloads]: Response is not JSON:', message);
                    throw new Error(message); // throw the error to make `.catch()` work due to response must be JSON only.
                }

                return response;
            })
            .then((response) => {
                if (typeof(response) === 'object') {
                    if (typeof(response.foundWebhook) !== 'undefined' && response.foundWebhook === true) {
                        thisTarget.innerHTML = '<i class="rddownloads_icon-webhook-status fas fa-check"></i> ' + RdDownloads.txtExists;
                    } else {
                        thisTarget.innerHTML = '<i class="rddownloads_icon-webhook-status fas fa-times"></i> ' + RdDownloads.txtNotExists;
                    }
                }

                return Promise.resolve(response);
            })
            .catch((response) => {
                const formResultHTML = rdDownloadsGetNoticeElement('notice-error', response);

                formResultPlaceholder.innerHTML = formResultHTML;
                formResultPlaceholder.scrollIntoView({'behavior': 'smooth'});

                statusIcon.className = '';
                statusIcon.classList.add('rddownloads_icon-webhook-status', 'fas', 'fa-solid', 'fa-question', 'fontawesome-icon');
            })
            ;
        });
    }// #listenClickCheckRepoWebhook


    /**
     * Listen click and force synchronize secret with user's repositories.
     * 
     * @since 1.0.14 Moved from `forceSyncSecret()` with new code that did not use jQuery.
     */
    #listenClickForceSyncSecret() {
        const forceSyncButton = document.getElementById('rddownloads_forcesync_github_secret');
        if (!forceSyncButton) {
            console.error('[rd-downloads]: Force sync secret button is not exists.');
            return;
        }

        forceSyncButton.addEventListener('click', (event) => {
            event.preventDefault();

            this.#enableButtons(false);

            const formResultPlaceholder = document.querySelector('.rd-downloads-form-result-placeholder');
            let formResultWorking = rdDownloadsGetNoticeElement('notice-warning', RdDownloads.txtSyncing);
            formResultPlaceholder.innerHTML = formResultWorking;
            formResultPlaceholder.scrollIntoView({'behavior': 'smooth'});

            const formData = new FormData();
            formData.set('security', RdDownloads.nonce);
            formData.set('action', 'RdDownloadsSyncGitHubSecretToAll');

            fetch(ajaxurl, {
                'method': 'POST',
                'headers': {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                'body': new URLSearchParams(formData),
            })
            .then(async (rawResponse) => {
                const contentType = rawResponse.headers.get('content-type');
                let response;
                if (contentType && contentType.includes('application/json')) {
                    response = await rawResponse.json();
                } else {
                    let message = await rawResponse.text();
                    if ('' === message) {
                        if (400 === rawResponse.status) {
                            message = 'Bad Request';
                        }
                    }
                    console.warn('[rd-downloads]: Response is not JSON:', message);
                    throw new Error(message); // throw the error to make `.catch()` work due to response must be JSON only.
                }

                return response;
            })
            .then((response) => {
                if (typeof(response.form_result_class) !== 'undefined' && typeof(response.form_result_msg) !== 'undefined') {
                    const formResultHTML = rdDownloadsGetNoticeElement(response.form_result_class, response.form_result_msg);

                    formResultPlaceholder.innerHTML = formResultHTML;
                    formResultPlaceholder.scrollIntoView({'behavior': 'smooth'});
                }
                return Promise.resolve(response);
            })
            .catch((response) => {
                const formResultHTML = rdDownloadsGetNoticeElement('notice-error', response);

                formResultPlaceholder.innerHTML = formResultHTML;
                formResultPlaceholder.scrollIntoView({'behavior': 'smooth'});
            })
            .finally(() => {
                this.#enableButtons();
            })
            ;
        });
    }// #listenClickForceSyncSecret


    /**
     * Re-generate secret and ajax save.
     * 
     * @since 1.0.14 Moved from `regenerateSecret()` with new code that did not use jQuery.
     */
    #listenClickRegenerateSecret() {
        const regenerateButton = document.getElementById('rddownloads_regenerate_secret');
        if (!regenerateButton) {
            console.error('[rd-downloads]: The re-generate button is not exists.');
            return;
        }

        regenerateButton.addEventListener('click', (event) => {
            const confirmVal = confirm(RdDownloads.txtAreYouSureRegenerateSecret);
            if (!confirmVal) {
                return;
            }

            const newSecret = RdDownloads.currentUserId + '_' + this.#randomString();
            const secretField = document.getElementById('rddownloads_githubwebhook_secret');
            if (!secretField) {
                console.error('[rd-downloads]: There is no secret field.');
                return;
            }
            secretField.value = newSecret;
            secretField.setAttribute('type', 'text');
            this.#enableButtons(false);

            // display re-generating message.
            const formResultWorking = rdDownloadsGetNoticeElement('notice-warning', RdDownloads.txtRegenerating);
            const formResultPlaceholder = document.querySelector('.rd-downloads-form-result-placeholder');
            formResultPlaceholder.innerHTML = formResultWorking;
            formResultPlaceholder.scrollIntoView({'behavior': 'smooth'});

            const formData = new FormData();
            formData.set('security', RdDownloads.nonce);
            formData.set('action', 'RdDownloadsNewGitHubSecret');
            formData.set('rddownloads_githubwebhook_secret', newSecret);

            fetch(ajaxurl, {
                'method': 'POST',
                'headers': {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                'body': new URLSearchParams(formData),
            })
            .then(async (rawResponse) => {
                const contentType = rawResponse.headers.get('content-type');
                let response;
                if (contentType && contentType.includes('application/json')) {
                    response = await rawResponse.json();
                } else {
                    let message = await rawResponse.text();
                    if ('' === message) {
                        if (400 === rawResponse.status) {
                            message = 'Bad Request';
                        }
                    }
                    console.warn('[rd-downloads]: Response is not JSON:', message);
                    throw new Error(message); // throw the error to make `.catch()` work due to response must be JSON only.
                }

                return response;
            })
            .then((response) => {
                if (typeof(response) === 'object') {
                    if (typeof(response.githubSecret) !== 'undefined') {
                        if (response.githubSecret !== newSecret) {
                            console.log('[rd-downloads]: JS generated secret: ' + newSecret + ', php generated secret: ' + response.githubSecret);
                        }
                        secretField.value = response.githubSecret;
                        secretField.setAttribute('type', 'text');
                    }
                }

                return Promise.resolve(response);
            })
            .then((response) => {
                if (typeof(response.form_result_class) !== 'undefined' && typeof(response.form_result_msg) !== 'undefined') {
                    const formResultHTML = rdDownloadsGetNoticeElement(response.form_result_class, response.form_result_msg);

                    formResultPlaceholder.innerHTML = formResultHTML;
                    formResultPlaceholder.scrollIntoView({'behavior': 'smooth'});
                }
                return Promise.resolve(response);
            })
            .catch((response) => {
                const formResultHTML = rdDownloadsGetNoticeElement('notice-error', response);

                formResultPlaceholder.innerHTML = formResultHTML;
                formResultPlaceholder.scrollIntoView({'behavior': 'smooth'});
            })
            .finally(() => {
                this.#enableButtons();
            })
            ;
        });// end addEventListener;
    }// #listenClickRegenerateSecret


    /**
     * Listen click to show/Hide secret field.
     * 
     * This method was called from `constructor()`.
     *
     * @since 1.0.14 Moved from `showHideSecret()` with new code that did not use jQuery.
     * @returns {undefined}
     */
    #listenClickShowHideSecret() {
        const showHideButton = document.getElementById('rddownloads_showhide_secret');
        const secretField = document.getElementById('rddownloads_githubwebhook_secret');
        if (!showHideButton || !secretField) {
            console.error('[rd-downloads]: There is no show/hide secret button or no secret field.');
            return;
        }

        showHideButton.addEventListener('click', (event) => {
            if (secretField.getAttribute('type') === 'password') {
                secretField.setAttribute('type', 'text');
            } else {
                secretField.setAttribute('type', 'password');
            }
        });
    }// #listenClickShowHideSecret


    /**
     * Generate random string.
     *
     * @since 1.0.14 Renamed from `_randomString()`.
     * @returns {String}
     */
    #randomString() {
        let text = "";
        let possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let totalChars = 20;

        for (var i = 0; i < totalChars; i++) {
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        }

        return text;
    }// #randomString


}


// on dom ready --------------------------------------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    let RdDownloadsGitHubOAuthClass = new RdDownloadsGitHubOAuth();
});
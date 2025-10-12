/**
 * Management JS.
 *
 * IE not supported.
 * 
 * @package rd-downloads
 */


/**
 * Download management page JS class.
 */
class RdDownloadsManagement {


    /**
     * @type {String} Download management form ID name.
     */
    #managementFormId = 'rd-downloads-list-items-form';


    /**
     * Class constructor of download management page.
     * 
     * @since 1.0.14
     */
    constructor() {
        this.#listenClickCopyShortcode();
        this.#listenClickSearchCleanForm();
        this.#listenClickSubmitBulkAction();
    }// constructor


    /**
     * Getter bulk action value.
     * 
     * @type {String}
     */
    get bulkActionValue() {
        const topBulkAction = document.querySelector('#' + this.#managementFormId + ' #bulk-action-selector-top');
        const bottomBulkAction = document.querySelector('#' + this.#managementFormId + ' #bulk-action-selector-bottom');

        let value = '';
        if (topBulkAction.value !== '-1') {
            value = topBulkAction.value;
        } else {
            value = bottomBulkAction.value;
        }

        return value;
    }// bulkActionValue


    /**
     * Enable or disable form elements such as buttons.
     * 
     * @since 1.0.14 Moved from `enableDisableButtons()` with new code that did not use jQuery.
     * @param {boolean} enable Set to `true` to enable elements, `false` to disable them.
     */
    #enableButtons(enable = true) {
        if (typeof(enable) !== 'boolean') {
            enable = true;
        }

        const thisForm = document.getElementById(this.#managementFormId);
        thisForm.querySelectorAll('button, input, select')?.forEach((item) => {
            if (true === enable) {
                item.disabled = false;
            } else if (false === enable) {
                item.disabled = true;
            }
        });
    }// #enableButtons


    /**
     * Listen click and then copy shortcode.
     * 
     * This method was called from `constructor()`.
     * 
     * @since 1.0.14 Moved from `eventSelectAndCopyText()` with new code that did not use jQuery.
     */
    #listenClickCopyShortcode() {
        document.addEventListener('click', (event) => {
            let thisTarget = event.target;
            if (thisTarget.closest('#' + this.#managementFormId + ' .shortcode-text')) {
                thisTarget = thisTarget.closest('#' + this.#managementFormId + ' .shortcode-text');
                event.preventDefault();
            } else {
                return ;
            }

            // select all
            thisTarget.select();

            // copy
            try {
                var successful = document.execCommand('copy'); // some browser may not supported
                var msg = successful ? 'successful' : 'unsuccessful';
                console.debug('[rd-downloads]: Copying text command was ' + msg);
            } catch (ex) {
                console.warn('[rd-downloads]: Copy to clipboard failed.', ex);
            }

            // display that it was copied.
            const copiedMsgElement = thisTarget.parentElement.querySelector('.copied-msg');
            copiedMsgElement.classList.remove('hidden');
            // delay and hide it again.
            setTimeout(function() {
                copiedMsgElement.classList.add('hidden');
            }, 1500);
        });
    }// #listenClickCopyShortcode


    /**
     * Listen click on search button and cleanup form.
     * 
     * This method was called from `constructor()`.
     * 
     * @since 1.0.14 Moved from `eventSearchButtonCleanForm()` with new code that did not use jQuery.
     */
    #listenClickSearchCleanForm() {
        document.addEventListener('click', (event) => {
            const thisTarget = event.target;
            if (!thisTarget.closest('#' + this.#managementFormId + ' #search-submit')) {
                return ;
            }

            const thisForm = document.getElementById(this.#managementFormId);
            // uncheck all checkboxes.
            thisForm.querySelectorAll('input[type="checkbox"]:checked')?.forEach((item) => {
                item.checked = false;
            });
            // reset bulk actions select boxes.
            thisForm.querySelectorAll('[name="action"]')?.forEach((item) => {
                item.value = '-1';
            });
            thisForm.querySelectorAll('[name="action2"]')?.forEach((item) => {
                item.value = '-1';
            });
        });
    }// #listenClickSearchCleanForm


    /**
     * Listen click on submit bulk action.
     * 
     * This method was called from `constructor()`.
     * 
     * @since 1.0.14 Moved from `eventSubmitBulkActions()` with new code that did not use jQuery.
     */
    #listenClickSubmitBulkAction() {
        document.addEventListener('click', (event) => {
            if (event.target.closest('.action')) {
                event.preventDefault();
            } else {
                return;
            }

            const thisForm = event.target.closest('#' + this.#managementFormId);
            if (!thisForm) {
                return;
            }

            const bulkActionValue = this.bulkActionValue;
            let confirmVal = false;
            if ('delete' === bulkActionValue) {
                confirmVal = confirm(RdDownloads.txtAreYouSureDelete);
            } else {
                confirmVal = true;
            }

            if (false === confirmVal) {
                return;
            }

            const formResultPlaceholder = document.querySelector('.rd-downloads-form-result-placeholder');
            // clear result placeholder.
            if (formResultPlaceholder) {
                formResultPlaceholder.innerHTML = '';
            }
            // disable buttons.
            this.#enableButtons(false);

            const formData = new FormData();
            formData.set('security', RdDownloads.nonce);
            formData.set('action', 'RdDownloadsBulkActions');
            formData.set('bulkAction', bulkActionValue);
            thisForm.querySelectorAll('input[type="checkbox"][name="download_id[]"]')?.forEach((item) => {
                if (true === item.checked) {
                    formData.append('download_id[]', item.value);
                }
            });

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
                    console.warn('Response is not JSON:', message);
                    throw new Error(message); // throw the error to make `.catch()` work due to response must be JSON only.
                }

                return response;
            })
            .then((response) => {
                if (
                    typeof(response) !== 'undefined' && 
                    typeof(response.additionalResults) !== 'undefined' && 
                    typeof(response.additionalResults.deleted_download_ids) !== 'undefined'
                ) {
                    if (typeof(response.additionalResults.deleted_download_ids) === 'object') {
                        for (const download_id of response.additionalResults.deleted_download_ids) {
                            document.querySelector('.rd-downloads_download_id_' + download_id)?.remove();
                        }
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
                // re-enable buttons.
                this.#enableButtons();
            })
            ;
        });
    }// #listenClickSubmitBulkAction


}


// on dom ready --------------------------------------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    let RdDownloadsManagementClass = new RdDownloadsManagement();
});
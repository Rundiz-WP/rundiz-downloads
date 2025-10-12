

class RdDownloadLogs {


    /**
     * @type {String} Download logs form ID name.
     */
    #managementFormId = 'rd-download-logs-list-items-form';


    /**
     * Class constructor of download management logs.
     * 
     * @since 1.0.14
     */
    constructor() {
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
            if ('clearlogs' === bulkActionValue) {
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
            formData.set('action', 'RdDownloadsLogsBulkActions');
            formData.set('bulkAction', bulkActionValue);

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
                const logsTbody = document.querySelector('.downloads_page_rd-downloads_logs tbody');
                logsTbody.innerHTML = '';
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
    var RdDownloadLogsClass = new RdDownloadLogs();
});
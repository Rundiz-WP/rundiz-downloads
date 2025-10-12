

/**
 * Ajax clear all cached.
 *
 * @returns {undefined}
 */
function rdDownloadsSettingsAjaxClearCache() {
    const clearCacheButton = document.querySelector('#rd-downloads-settings-clear-cache');
    if (!clearCacheButton) {
        console.error('[rd-downloads]: The clear cache button does not exists on this page.');
        return;
    }

    clearCacheButton.addEventListener('click', (event) => {
        event.preventDefault();
        const thisTarget = event.target;

        thisTarget.querySelector('.icon-correct')?.remove();
        thisTarget.insertAdjacentHTML('afterbegin', '<i class="fas fa-spinner fa-pulse icon-loading"></i> ');

        const formData = new FormData();
        formData.set('security', RdDownloadsSettings.nonce);
        formData.set('action', 'RdDownloadsSettingsClearCache');

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
            thisTarget.insertAdjacentHTML('afterbegin', '<i class="fas fa-check icon-correct"></i> ');
        })
        .catch((response) => {
            alert(response);
        })
        .finally(() => {
            thisTarget.querySelector('.icon-loading')?.remove();
        })
        ;
    });
}// rdDownloadsSettingsAjaxClearCache


// on dom ready --------------------------------------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    rdDownloadsSettingsAjaxClearCache();
});
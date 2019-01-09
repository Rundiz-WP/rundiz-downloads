

/**
 * Ajax clear all cached.
 *
 * @returns {undefined}
 */
function rdDownloadsSettingsAjaxClearCache() {
    var $ = jQuery.noConflict();

    $('#rd-downloads-settings-clear-cache').off('click');
    $('#rd-downloads-settings-clear-cache').on('click', function(e) {
        e.preventDefault();

        $(this).find('.icon-correct').remove();
        $(this).prepend('<i class="fas fa-spinner fa-pulse icon-loading"></i> ');

        $.ajax({
            'url': ajaxurl,
            'method': 'POST',
            'data': 'security=' + encodeURIComponent(RdDownloadsSettings.nonce) + '&action=RdDownloadsSettingsClearCache',
            'dataType': 'json'
        })
        .done(function() {
            $('#rd-downloads-settings-clear-cache .icon-loading').remove();
            $('#rd-downloads-settings-clear-cache').prepend('<i class="fas fa-check icon-correct"></i>');
        })
        .fail(function() {
            $('#rd-downloads-settings-clear-cache .icon-loading').remove();
        })
        .always(function(jqXHR, textStatus, data) {
            if (typeof(jqXHR) !== 'undefined' && typeof(jqXHR.responseJSON) !== 'undefined') {
                var response = jqXHR.responseJSON;
            } else {
                var response = jqXHR;
            }
            if (typeof(response) === 'undefined') {
                response = {};
            }

            if (typeof(response.form_result_msg) !== 'undefined') {
                alert(response.form_result_msg);
            }

            response = undefined;
        });
    });
}// rdDownloadsSettingsAjaxClearCache


// on dom ready --------------------------------------------------------------------------------------------------------
(function($) {
    rdDownloadsSettingsAjaxClearCache();
})(jQuery);


/**
 * Ajax test GitHub token.
 * 
 * @returns {undefined}
 */
function rdDownloadsSettingsAjaxTestToken() {
    var $ = jQuery.noConflict();

    $('#rd-downloads-settings-test-token').off('click');
    $('#rd-downloads-settings-test-token').on('click', function(e) {
        e.preventDefault();

        $(this).find('.icon-correct').remove();
        $(this).prepend('<i class="fas fa-spinner fa-pulse icon-loading"></i> ');

        $.ajax({
            'url': ajaxurl,
            'method': 'POST',
            'data': 'security=' + RdDownloadsSettings.nonce + '&action=RdDownloadsSettingsTestGithubToken&token=' + $('#rdd_github_token').val(),
            'dataType': 'json'
        })
        .done(function() {
            $('#rd-downloads-settings-test-token .icon-loading').remove();
            $('#rd-downloads-settings-test-token').prepend('<i class="fas fa-check icon-correct"></i>');
        })
        .fail(function() {
            $('#rd-downloads-settings-test-token .icon-loading').remove();
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
}// rdDownloadsSettingsAjaxTestToken


/**
 * Re-generate secret for GitHub.
 * 
 * @link https://stackoverflow.com/a/1349426/128761 Copied source from here.
 * @returns {undefined}
 */
function rdDownloadsSettingsRegenerateSecret() {
    var $ = jQuery.noConflict();

    $('#rd-downloads-settings-regenerate-secret').off('click');
    $('#rd-downloads-settings-regenerate-secret').on('click', function(e) {
        e.preventDefault();

        var confirmVal = confirm(RdDownloadsSettings.txtAreYouSureRegenerateSecret);

        if (confirmVal === true) {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            var totalChars = 20;

            for (var i = 0; i < totalChars; i++) {
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            }

            $('#rdd_github_secret').val(text);
        }
    });
}// rdDownloadsSettingsRegenerateSecret


// on dom ready --------------------------------------------------------------------------------------------------------
(function($) {
    rdDownloadsSettingsAjaxTestToken();
    rdDownloadsSettingsRegenerateSecret();
})(jQuery);
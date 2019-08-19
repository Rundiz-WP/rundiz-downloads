

class RdDownloadLogs {


    /**
     * Get bulk action value.
     * @type type
     */
    get bulkActionValue() {
        var $ = jQuery.noConflict();

        var topBulkActionValue = $('#rd-download-logs-list-items-form #bulk-action-selector-top').val();
        var bottomBulkActionValue = $('#rd-download-logs-list-items-form #bulk-action-selector-bottom').val();
        var bulkActionValue;
        if (topBulkActionValue !== '-1') {
            bulkActionValue = topBulkActionValue;
        } else {
            bulkActionValue = bottomBulkActionValue;
        }

        return bulkActionValue;
    }// bulkActionValue


    /**
     * Enable or disable buttons, form controls.
     *
     * @param {boolean} enable Set to `true` to enable buttons, set to `false` to disable them. Default is `true`.
     * @returns {undefined}
     */
    enableDisableButtons(enable = true) {
        var $ = jQuery.noConflict();

        if (typeof(enable) === 'undefined') {
            enable = true;
        }

        if (enable === true) {
            $('#rd-download-logs-list-items-form .button.action').prop('disabled', false);
            $('#rd-download-logs-list-items-form #search-submit').prop('disabled', false);
        } else if (enable === false) {
            $('#rd-download-logs-list-items-form .button.action').prop('disabled', true);
            $('#rd-download-logs-list-items-form #search-submit').prop('disabled', true);
        }
    }// enableDisableButtons


    /**
     * Listen to select bulk action and set from bottom or top to be the same value.
     *
     * @returns {undefined}
     */
    eventSelectBulkActionBothSameValue() {
        var $ = jQuery.noConflict();

        $('#rd-download-logs-list-items-form #bulk-action-selector-bottom, #rd-download-logs-list-items-form #bulk-action-selector-top').off('change');
        // change on the bottom action, set top to the same.
        $('#rd-download-logs-list-items-form #bulk-action-selector-bottom').on('change', function() {
            $('#rd-download-logs-list-items-form #bulk-action-selector-top').val($(this).val());
        });
        // change on the top action, set bottom to the same.
        $('#rd-download-logs-list-items-form #bulk-action-selector-top').on('change', function() {
            $('#rd-download-logs-list-items-form #bulk-action-selector-bottom').val($(this).val());
        });
    }// eventSelectBulkActionBothSameValue


    /**
     * Listen to form submit for bulk actions, prevent it and use ajax instead.
     *
     * @returns {undefined}
     */
    eventSubmitBulkActions() {
        var $ = jQuery.noConflict();
        var thisClass = this;

        $('#rd-download-logs-list-items-form #doaction, #rd-download-logs-list-items-form #doaction2').off('click');
        $('#rd-download-logs-list-items-form #doaction, #rd-download-logs-list-items-form #doaction2').on('click', function(e) {
            e.preventDefault();

            var bulkActionValue = thisClass.bulkActionValue;
            if (bulkActionValue === 'clearlogs') {
                var confirmVal = confirm(RdDownloads.txtAreYouSureDelete);
            } else if (bulkActionValue != '-1' && bulkActionValue != '') {
                var confirmVal = true;
            }

            if (confirmVal === true) {
                // clear result placeholder.
                $('.rd-downloads-form-result-placeholder').html('');
                // disable buttons.
                thisClass.enableDisableButtons(false);
                var formData = 'security=' + encodeURIComponent(RdDownloads.nonce) + '&action=RdDownloadsLogsBulkActions&bulkAction=' + encodeURIComponent(bulkActionValue);

                $.ajax({
                    'url': ajaxurl,
                    'method': 'POST',
                    'data': formData,
                    'dataType': 'json'
                })
                .done(function(data, textStatus, jqXHR) {
                    if (typeof(data) !== 'undefined' && typeof(data.responseJSON) !== 'undefined') {
                        var response = data.responseJSON;
                    } else {
                        var response = data;
                    }
                    if (typeof(response) === 'undefined') {
                        response = {};
                    }

                    response = undefined;
                })
                .always(function(data, textStatus, jqXHR) {
                    if (typeof(data) !== 'undefined' && typeof(data.responseJSON) !== 'undefined') {
                        var response = data.responseJSON;
                    } else {
                        var response = data;
                    }
                    if (typeof(response) === 'undefined') {
                        response = {};
                    }

                    if (typeof(response.form_result_class) !== 'undefined' && typeof(response.form_result_msg) !== 'undefined') {
                        var form_result_html = rdDownloadsGetNoticeElement(response.form_result_class, response.form_result_msg);

                        $('.rd-downloads-form-result-placeholder').html(form_result_html);
                        $('html, body').animate({
                            scrollTop: ($('.rd-downloads-form-result-placeholder').first().offset().top - 50)
                        },500);

                        rdDownloadsReActiveDismissable();
                    }

                    // enable buttons
                    thisClass.enableDisableButtons(true);

                    response = undefined;
                });
            }
        });
    }// eventSubmitBulkActions


}


// on dom ready --------------------------------------------------------------------------------------------------------
(function ($) {
    var RdDownloadLogsClass = new RdDownloadLogs();

    // set both bulk action the same value.
    RdDownloadLogsClass.eventSelectBulkActionBothSameValue();

    // bulk action submit using ajax.
    RdDownloadLogsClass.eventSubmitBulkActions();
})(jQuery);
/**
 * Management JS.
 *
 * IE not supported.
 */


class RdDownloadsManagement {


    /**
     * Get bulk action value.
     * @type type
     */
    get bulkActionValue() {
        var $ = jQuery.noConflict();

        var topBulkActionValue = $('#rd-downloads-list-items-form #bulk-action-selector-top').val();
        var bottomBulkActionValue = $('#rd-downloads-list-items-form #bulk-action-selector-bottom').val();
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
            $('#rd-downloads-list-items-form .button.action').prop('disabled', false);
            $('#rd-downloads-list-items-form #search-submit').prop('disabled', false);
        } else if (enable === false) {
            $('#rd-downloads-list-items-form .button.action').prop('disabled', true);
            $('#rd-downloads-list-items-form #search-submit').prop('disabled', true);
        }
    }// enableDisableButtons


    /**
     * Listen to search button click and then clean up checkbox, bulk actions select boxes.
     *
     * @returns {undefined}
     */
    eventSearchButtonCleanForm() {
        var $ = jQuery.noConflict();

        $('#rd-downloads-list-items-form #search-submit').off('click');
        $('#rd-downloads-list-items-form #search-submit').on('click', function() {
            // uncheck all checkboxes.
            $('#rd-downloads-list-items-form input[type="checkbox"]').prop('checked', false);
            // reset bulk actions select boxes.
            $('#rd-downloads-list-items-form #bulk-action-selector-bottom, #rd-downloads-list-items-form #bulk-action-selector-top').val('-1');
        });
    }// eventSearchButtonCleanForm


    /**
     * Listen to short code click and copy.
     *
     * @link https://stackoverflow.com/a/15731900/128761 Fade out and then add hidden class.
     * @link https://stackoverflow.com/a/20372695/128761 Remove inline style reference.
     * @returns {undefined}
     */
    eventSelectAndCopyText() {
        var $ = jQuery.noConflict();
        var thisClass = this;

        $('#rd-downloads-list-items-form .shortcode-text').off('click');
        $('#rd-downloads-list-items-form .shortcode-text').on('click', function() {
            // select the whole shortcode.
            thisClass.selectText($(this)[0]);

            // then copy it.
            try {
                var successful = document.execCommand('copy');  // Security exception may be thrown by some browsers.
                var msg = successful ? 'successful' : 'unsuccessful';
                console.log('Copying text command was ' + msg);
            } catch (ex) {
                console.warn("Copy to clipboard failed.", ex);
            }

            // display that it was copied.
            var copiedMsgElement = $(this).siblings('.copied-msg');
            copiedMsgElement.removeClass('hidden');
            // delay and hide it again.
            setTimeout(function() {
                copiedMsgElement.fadeOut('fast', function() {
                    $(this).addClass('hidden').removeAttr('style');
                });
            }, 1500);
        });
    }// eventSelectAndCopyText


    /**
     * Listen to select bulk action and set from bottom or top to be the same value.
     *
     * @returns {undefined}
     */
    eventSelectBulkActionBothSameValue() {
        var $ = jQuery.noConflict();

        $('#rd-downloads-list-items-form #bulk-action-selector-bottom, #rd-downloads-list-items-form #bulk-action-selector-top').off('change');
        // change on the bottom action, set top to the same.
        $('#rd-downloads-list-items-form #bulk-action-selector-bottom').on('change', function() {
            $('#rd-downloads-list-items-form #bulk-action-selector-top').val($(this).val());
        });
        // change on the top action, set bottom to the same.
        $('#rd-downloads-list-items-form #bulk-action-selector-top').on('change', function() {
            $('#rd-downloads-list-items-form #bulk-action-selector-bottom').val($(this).val());
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

        $('#rd-downloads-list-items-form #doaction, #rd-downloads-list-items-form #doaction2').off('click');
        $('#rd-downloads-list-items-form #doaction, #rd-downloads-list-items-form #doaction2').on('click', function(e) {
            e.preventDefault();

            var bulkActionValue = thisClass.bulkActionValue;
            if (bulkActionValue === 'delete') {
                var confirmVal = confirm(RdDownloads.txtAreYouSureDelete);
            } else if (bulkActionValue != '-1' && bulkActionValue != '') {
                var confirmVal = true;
            }

            if (confirmVal === true) {
                // clear result placeholder.
                $('.rd-downloads-form-result-placeholder').html('');
                // disable buttons.
                thisClass.enableDisableButtons(false);
                var formData = 'security=' + encodeURIComponent(RdDownloads.nonce) + '&action=RdDownloadsBulkActions&bulkAction=' + encodeURIComponent(bulkActionValue) + '&' + $('#rd-downloads-list-items-form input[type="checkbox"]').serialize();

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
                    if (typeof(response) === 'undefined' || response === null || response === '') {
                        response = {};
                    }

                    if (typeof(response) !== 'undefined' && typeof(response.additionalResults) !== 'undefined' && typeof(response.additionalResults.deleted_download_ids) !== 'undefined') {
                        if (typeof(response.additionalResults.deleted_download_ids) === 'object') {
                            $.each(response.additionalResults.deleted_download_ids, function(index, download_id) {
                                $('.rd-downloads_download_id_' + download_id).remove();
                            });
                        }
                    }

                    response = undefined;
                })
                .always(function(data, textStatus, jqXHR) {
                    if (typeof(data) !== 'undefined' && typeof(data.responseJSON) !== 'undefined') {
                        var response = data.responseJSON;
                    } else {
                        var response = data;
                    }
                    if (typeof(response) === 'undefined' || response === null || response === '') {
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


    /**
     * Select all text in target element.
     *
     * @link https://stackoverflow.com/a/987376/128761 Code copied from here.
     * @param {object} node The element object. Example: $('#target')[0];
     * @returns {undefined}
     */
    selectText(node) {
        if (node.nodeName === 'INPUT') {
            // it is selecting text from `<input>` element.
            node.setSelectionRange(0, node.value.length);
        } else {
            // it is selecting text from other element.
            if (document.body.createTextRange) {
                const range = document.body.createTextRange();
                range.moveToElementText(node);
                range.select();
            } else if (window.getSelection) {
                const selection = window.getSelection();
                const range = document.createRange();
                range.selectNodeContents(node);
                selection.removeAllRanges();
                selection.addRange(range);
            } else {
                console.warn("Could not select text in node: Unsupported browser.");
            }
        }
    }// selectText


}


// on dom ready --------------------------------------------------------------------------------------------------------
(function ($) {
    let RdDownloadsManagementClass = new RdDownloadsManagement();

    // click and copy shortcode.
    RdDownloadsManagementClass.eventSelectAndCopyText();

    // click on search then clean up form.
    RdDownloadsManagementClass.eventSearchButtonCleanForm();

    // set both bulk action the same value.
    RdDownloadsManagementClass.eventSelectBulkActionBothSameValue();

    // bulk action submit using ajax.
    RdDownloadsManagementClass.eventSubmitBulkActions();
})(jQuery);
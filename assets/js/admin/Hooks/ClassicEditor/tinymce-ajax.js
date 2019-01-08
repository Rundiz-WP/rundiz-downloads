/**
 * Rundiz Downloads ajax for working with TinyMCE dialog.
 */


/**
 * Ajax search.
 *
 * @param {string} searchValue
 * @returns {undefined}
 */
function rdDownloadsAjaxSearch(searchValue, page = 1) {
    var $ = jQuery.noConflict();

    $('.rd-downloads-inside-input-icon').removeClass('hidden');
    $('#rd-downloads-search-result').html('');// clear previous results.

    if (isNaN(page)) {
        console.log('isnan:'+page);
        page = 1;
    }

    $.ajax({
        'url': ajaxurl,
        'method': 'GET',
        'data': 'security=' + encodeURIComponent(RdDownloads.nonce) + '&action=RdDownloadsBrowserSearch&search=' + encodeURIComponent(searchValue) + '&page=' + page,
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

        if (
            typeof(response.total) !== 'undefined' &&
            typeof(response.results) !== 'undefined' &&
            typeof(response.per_page) !== 'undefined' &&
            typeof(response.current_page) !== 'undefined' &&
            typeof(response.total_pages) !== 'undefined'
        ) {
            var Template = wp.template('rd-downloads-search-table-result');
            response.previous_page = ((parseInt(response.current_page) - 1) >= 1 ? (parseInt(response.current_page) - 1) : 1);
            response.next_page = ((parseInt(response.current_page) + 1) <= response.total_pages ? (parseInt(response.current_page) + 1) : response.total_pages);
            console.log(parseInt(response.current_page) + 1);
            $('#rd-downloads-search-result').html(Template(response));

            var TemplateResultItem = wp.template('rd-downloads-search-list-item');
            $.each(response.results, function(index, item) {
                item.size = rdDownloadsHumanFileSize(item.download_size, true);
                if (item.download_type == '0') {
                    item.type = RdDownloads.txtLocalFile;
                } else if (item.download_type == '1') {
                    item.type = RdDownloads.txtGitHubFile;
                } else if (item.download_type == '2') {
                    item.type = RdDownloads.txtAnyRemoteFile;
                } else {
                    item.type = RdDownloads.txtUnknow;
                }
                $('#rd-downloads-search-result table tbody').append(TemplateResultItem(item));
            });
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
            tinymce.activeEditor.windowManager.alert(response.form_result_msg);
        }

        $('.rd-downloads-inside-input-icon').addClass('hidden');
    });

    return false;
}// rdDownloadsAjaxSearch


/**
 * Return generated HTML for TinyMCE dialog content.
 *
 * @returns {RdDownloads.customDialogContent}
 */
function rdDownloadsCreateDialogContent() {
    return RdDownloads.customDialogContent;
}// rdDownloadsCreateDialogContent


/**
 * Search download.
 *
 * This function was called from tinymce-dialog.js
 *
 * @returns {undefined}
 */
function rdDownloadsListenSearchInput() {
    var $ = jQuery.noConflict();
    var typingTimer;// timer identifier.
    var doneTypingInterval = 800; // delay in seconds (1 second is 1000, the value is 1000).
    console.log('rdDownloadsListenSearchInput called');

    $('#rd-downloads-search-input').off('keyup keydown');
    $('#rd-downloads-search-input').on('keyup', function(e) {
        clearTimeout(typingTimer);
        console.log('search keyup event.');

        var inputValue = $(this).val();

        typingTimer = setTimeout(function() {
            rdDownloadsAjaxSearch(inputValue);
        }, doneTypingInterval);
    });

    $('#rd-downloads-search-input').on('keydown', function() {
        clearTimeout(typingTimer);
    });
}// rdDownloadsListenSearchInput
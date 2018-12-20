

/**
 * Ajax permanently delete a file.
 * 
 * @param {string} target
 * @param {string} previousId
 * @returns {undefined}
 */
function rdDownloadsAjaxDeleteFile(target, previousId) {
    var $ = jQuery.noConflict();
    var confirmVal = confirm(RdDownloads.txtAreYouSureDeleteFileUndone);

    if (confirmVal === true) {
        // clear result placeholder.
        $('.rd-downloads-form-result-placeholder').html('');
        // disable buttons.
        rdDownloadsEnableDisableButtons(false);

        target = rdDownloadsUnEscapeHtml(target);
        var formData = 'security=' + RdDownloads.nonce + '&action=RdDownloadsDeleteFile&download_id=' + $('#rd-downloads-edit-form #download_id').val() + '&target=' + target;

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

            if (typeof(response.deleted) !== 'undefined' && response.deleted === true) {
                $('#' + previousId).remove();
            }

            if (typeof(response.deleteUrl) !== 'undefined' && response.deleteUrl === $('#rd-downloads-edit-form #download_url').val()) {
                $('#rd-downloads-edit-form #download_url').val('');
                $('#rd-downloads-edit-form .download_size').html('');
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

            // enable buttons.
            rdDownloadsEnableDisableButtons();

            response = undefined;
        });
    }
}// rdDownloadsAjaxDeleteFile


/**
 * Ajax file browser.
 * 
 * @param {string} target
 * @param {string} previousId
 * @returns {Boolean}
 */
function rdDownloadsAjaxFileBrowser(target, previousId) {
    var $ = jQuery.noConflict();

    if (typeof(target) === 'undefined') {
        target = '';
    }
    if (typeof(previousId) === 'undefined') {
        previousId = '';
    }

    // clear result placeholder.
    $('.rd-downloads-form-result-placeholder').html('');

    target = rdDownloadsUnEscapeHtml(target);
    var formData = 'security=' + RdDownloads.nonce + '&action=RdDownloadsBrowseFiles&target=' + target;

    if (previousId !== '' && $('#' + previousId).hasClass('is-open')) {
        // if folder is openning, close it.
        $('#' + previousId).find('ul').remove();
        $('#' + previousId).removeClass('is-open');
        // remove folder open icon, add folder close icon
        $('#' + previousId + ' > a .icon-folder').removeClass('fa-folder-open').addClass('fa-folder');
        return false;
    }

    $('.rd-downloads-reload-button .icon-reload').addClass('fa-spin');

    $.ajax({
        'url': ajaxurl,
        'method': 'GET',
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

        if (typeof(response.list) !== 'undefined') {
            if (target === '') {
                $('.rd-downloads-form-type-local-file-browser').html('<ul class="main-folder"></ul>');
            } else {
                if (previousId !== '') {
                    if ($('#' + previousId + ' ul').length == 0) {
                        $('#' + previousId).append('<ul></ul>');
                    }
                    // folder is currently closed, open it.
                    $('#' + previousId).addClass('is-open');
                    // remove close folder icon, add open folder icon.
                    $('#' + previousId + ' > a .icon-folder').removeClass('fa-folder').addClass('fa-folder-open');
                }
            }

            var listHtml = '';
            var Template = wp.template('file-browser-list-item');
            $.each(response.list, function(index, item) {
                if (typeof(item.size) !== 'undefined') {
                    item.readableFileSize = rdDownloadsHumanFileSize(item.size, true);
                }
                if (typeof(item.previousTarget) !== 'undefined') {
                    item.previousTargetEscaped = rdDownloadsEscapeHtml(item.previousTarget);
                }
                if (typeof(item.relatedPath) !== 'undefined') {
                    item.relatedPathEscaped = rdDownloadsEscapeHtml(item.relatedPath);
                }

                var parsedHtml = Template(item);
                if (target === '') {
                    $('.rd-downloads-form-type-local-file-browser .main-folder').append(parsedHtml);
                } else {
                    if (previousId !== '') {
                        $('#' + previousId + ' ul').append(parsedHtml);
                    }
                }
            });// end .each response.list
            listHtml = undefined;
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

        $('.rd-downloads-reload-button .icon-reload').removeClass('fa-spin');

        response = undefined;
    });

    return false;
}// rdDownloadsAjaxFileBrowser


/**
 * Ajax get remote file size (including GitHub) on manually enter download URL.
 * 
 * @returns {undefined}
 */
function rdDownloadsAjaxGetRemoteFileSize() {
    var $ = jQuery.noConflict();
    var typingTimer;// timer identifier.
    var doneTypingInterval = 800; // delay in seconds (1 second is 1000, the value is 1000).

    $('#rd-downloads-edit-form #download_url').off('keyup keydown');
    $('#rd-downloads-edit-form #download_url').on('keyup', function(e) {
        clearTimeout(typingTimer);

        var inputValue = $(this).val();

        // always clear related path on manually enter download URL.
        $('#rd-downloads-edit-form #download_related_path').val('');
        // always hide force download because user is manually enter download URL.
        $('#rd-downloads-edit-form .rd-downloads-opt_force_download').addClass('hidden');

        if (rdDownloadsExtractRootDomain(inputValue).toLowerCase() == 'github.com') {
            // if found github.com link.
            $('#rd-downloads-edit-form #download_type').val(1);
            typingTimer = setTimeout(function() {
                getGitHubCorrectData(inputValue);
            }, doneTypingInterval);
        } else if (inputValue.indexOf('//') !== -1) {
            // if not found github.com link.
            $('#rd-downloads-edit-form #download_type').val(2);
            typingTimer = setTimeout(function() {
                getRemoteSize(inputValue);
            }, doneTypingInterval);
        }
    });

    $('#rd-downloads-edit-form #download_url').off('change');
    $('#rd-downloads-edit-form #download_url').on('change', function() {
        $(this).trigger('keyup');
    });

    $('#rd-downloads-edit-form #download_url').on('keydown', function() {
        clearTimeout(typingTimer);
    });

    /**
     * Get GitHub correct URL and maybe its file size.
     * 
     * @returns {undefined}
     */
    function getGitHubCorrectData(url) {
        // clear result placeholder.
        $('.rd-downloads-form-result-placeholder').html('');
        // add loading icon.
        $('#rd-downloads-edit-form .download_size').html('<i class="fas fa-spinner fa-pulse icon-loading"></i>');
        // disable buttons.
        rdDownloadsEnableDisableButtons(false);

        $.ajax({
            'url': ajaxurl,
            'method': 'GET',
            'data': 'security=' + RdDownloads.nonce + '&action=RdDownloadsGetGithubFileData&remote_file=' + url,
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

            console.log('Success get github data');

            if (typeof(response.size) !== 'undefined' && response.size >= 0) {
                var Template = wp.template('selected-download-file-size');
                $('#rd-downloads-edit-form #download_size').html(Template({
                    'size': rdDownloadsHumanFileSize(response.size, true),
                    'url': (typeof(response.url) !== 'undefined' ? response.url : url)
                }));
            } else {
                $('#rd-downloads-edit-form #download_size').html('');
            }

            if (typeof(response.url) !== 'undefined') {
                $('#rd-downloads-edit-form #download_url').val(response.url);
            }

            response = undefined;
        })
        .fail(function(jqXHR, textStatus, data) {
            $('#rd-downloads-edit-form #download_size').html('');
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
                }, 500);

                rdDownloadsReActiveDismissable();
            }

            rdDownloadsEnableDisableButtons();
        });
    }// getGitHubCorrectData

    /**
     * Begins get remote file size.
     * 
     * @returns {undefined}
     */
    function getRemoteSize(url) {
        // clear result placeholder.
        $('.rd-downloads-form-result-placeholder').html('');
        // add loading icon.
        $('#rd-downloads-edit-form .download_size').html('<i class="fas fa-spinner fa-pulse icon-loading"></i>');
        // disable buttons.
        rdDownloadsEnableDisableButtons(false);

        $.ajax({
            'url': ajaxurl,
            'method': 'GET',
            'data': 'security=' + RdDownloads.nonce + '&action=RdDownloadsGetRemoteFileData&remote_file=' + url,
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

            console.log('Success get remote size');

            if (typeof(response.size) !== 'undefined' && response.size >= 0) {
                var Template = wp.template('selected-download-file-size');
                $('#rd-downloads-edit-form .download_size').html(Template({
                    'size': rdDownloadsHumanFileSize(response.size, true),
                    'url': url
                }));
            } else {
                $('#rd-downloads-edit-form .download_size').html('');
            }

            response = undefined;
        })
        .fail(function(jqXHR, textStatus, data) {
            $('#rd-downloads-edit-form .download_size').html('');
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
                }, 500);

                rdDownloadsReActiveDismissable();
            }

            rdDownloadsEnableDisableButtons();
        });
    }// getRemoteSize
}// rdDownloadsAjaxGetRemoteFileSize


/**
 * Ajax save form.
 * 
 * @returns {undefined}
 */
function rdDownloadsAjaxSaveForm() {
    var $ = jQuery.noConflict();

    $('#rd-downloads-edit-form').off('submit');
    $('#rd-downloads-edit-form').on('submit', function(e) {
        e.preventDefault();
        if ($('.rd-downloads-save-form-button').is(':disabled')) {
            return false;
        }

        doSaveForm();
    });

    /**
     * Do the ajax saving data (insert/update in one function).
     * 
     * @returns {undefined}
     */
    function doSaveForm() {
        // clear result placeholder.
        $('.rd-downloads-form-result-placeholder').html('');
        // disable buttons.
        rdDownloadsEnableDisableButtons(false);

        $.ajax({
            'url': ajaxurl,
            'method': 'POST',
            'data': $('#rd-downloads-edit-form').serialize() + '&security=' + RdDownloads.savenonce + '&action=RdDownloadsSaveData',
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

            if (typeof(response) !== 'undefined') {
                if (typeof(response.insertResult) !== 'undefined' && typeof(response.editUrl) !== 'undefined') {
                    // if using insert and success.
                    console.log('Will be redirecting to edit page in 2 seconds.');
                    rdDownloadsEnableDisableButtons(false);
                    var editUrl = response.editUrl;
                    setTimeout(function() {
                        window.location.href = editUrl;
                    }, 2000);
                } else if (typeof(response.updateResult) !== 'undefined' && typeof(response.last_update) !== 'undefined') {
                    // if using update and success.
                    rdDownloadsEnableDisableButtons();
                    $('#rd-downloads-edit-form .last-update').html(response.last_update);
                } else {
                    rdDownloadsEnableDisableButtons();
                }
            }

            response = undefined;
        })
        .fail(function() {
            rdDownloadsEnableDisableButtons();
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
                }, 500);

                rdDownloadsReActiveDismissable();
            }
        });
    }// doSaveForm
}// rdDownloadsAjaxSaveForm


/**
 * Auto input file upload using ajax.
 * 
 * @returns {undefined}
 */
function rdDownloadsAutoUpload() {
    var $ = jQuery.noConflict();

    // variable for pending selected file to be upload.
    var uploadFilePending = [];

    // detect input file on selected then start upload.
    $('.rd-downloads-file-upload-button input[type="file"]').on('change', function(e) {
        // put any selected files into array variable.
        for (var i = 0; i < 1; ++i) {
            var file = e.target.files[i];
            uploadFilePending.push(file);
        }

        // do ajax upload.
        doUpload();
    });

    // detect drag drop upload.
    $('.rd-downloads-form-type-local-file-browser.rd-downloads-dropzone').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
        e.stopPropagation();
        e.preventDefault();
    })
    .on('dragover dragenter', function() {
        $(this).addClass('is-dragover');
    })
    .on('dragleave dragend drop', function() {
        $(this).removeClass('is-dragover');
    })
    .on('drop', function(e) {
        // put any selected files into array variable.
        for (var i = 0; i < 1; ++i) {
            var file = e.originalEvent.dataTransfer.files[i];
            uploadFilePending.push(file);
        }

        // do ajax upload.
        doUpload();
    });

    /**
     * Do ajax upload.
     * 
     * @returns {undefined}
     */
    function doUpload() {
        // clear result placeholder.
        $('.rd-downloads-form-result-placeholder').html('');
        // disable buttons.
        rdDownloadsEnableDisableButtons(false);
        // add uploading animate icon.
        $('<i class="fas fa-spinner fa-pulse icon-uploading"></i>').insertAfter('.rd-downloads-file-upload-button .icon-upload');

        var formData = new FormData();
        formData.append('security', RdDownloads.nonce);
        formData.append('action', 'RdDownloadsUploadFile');
        formData.append('download_id', $('#rd-downloads-edit-form #download_id').val());
        // set selected file from variable.
        $.each(uploadFilePending, function(index, file) {
            formData.append('upload_file', file, file.name);
        });

        $.ajax({
            'url': ajaxurl,
            'method': 'POST',
            'data': formData,
            'dataType': 'json',
            // Options to tell jQuery not to process data or worry about content-type. (For ajax upload or required for using with FormData object).
            'cache': false,
            'contentType': false,
            'processData': false,
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

            if (typeof(response.insert) !== 'undefined' && response.insert === true && typeof(response.download_id) !== 'undefined') {
                $('#rd-downloads-edit-form #download_id').val(response.download_id);
            }

            if (typeof(response.uploadSuccess) !== 'undefined' && response.uploadSuccess === true) {
                // set download url value.
                if (typeof(response.download_url) !== 'undefined') {
                    $('#rd-downloads-edit-form #download_type').val(0);
                    $('#rd-downloads-edit-form #download_url').val(response.download_url);
                }

                // display download file size and its link preview.
                if (typeof(response.download_size) !== 'undefined' && typeof(response.download_url) !== 'undefined') {
                    var Template = wp.template('selected-download-file-size');
                    $('#rd-downloads-edit-form .download_size').html(Template({
                        'size': rdDownloadsHumanFileSize(response.download_size, true),
                        'url': response.download_url
                    }));
                }

                // set hidden related path value.
                if (typeof(response.relatedPath) !== 'undefined') {
                    $('#rd-downloads-edit-form #download_related_path').val(response.relatedPath);
                }

                // ajax reload its folder if exists.
                if (typeof(response.parentDir) !== 'undefined' && typeof(response.parentId) !== 'undefined') {
                    if ($('#' + response.parentId).length != 0) {
                        if ($('#' + response.parentId).hasClass('is-open')) {
                            console.log('Due to the folder is currently open and it will be trigger as close. So, trigger it to close first then sending command again will be reload.');
                            rdDownloadsAjaxFileBrowser(encodeURIComponent(response.parentDir), response.parentId);
                        }
                        console.log('Sending command to reload its folder.');
                        rdDownloadsAjaxFileBrowser(encodeURIComponent(response.parentDir), response.parentId);
                    } else {
                        console.log('The parent of this folder is not showing (not even open or close), skip it.');
                    }
                }

                // show option force download because this user use upload.
                $('#rd-downloads-edit-form .rd-downloads-opt_force_download').removeClass('hidden');
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

            // enable buttons.
            rdDownloadsEnableDisableButtons();
            // remove uploading animate icon.
            $('.rd-downloads-file-upload-button .icon-uploading').remove();

            response = undefined;
        });

        formData = undefined;

        // reset input file.
        resetInputFile($('#rd-downloads-local-input-file'));
    }// doUpload


    /**
     * Reset input file.
     * 
     * @link https://stackoverflow.com/a/13351234/128761 Reference.
     * @param {object} e
     * @returns {undefined}
     */
    function resetInputFile(e) {
        e.wrap('<form>').closest('form').get(0).reset();
        e.unwrap();
        e.val('');// for reset File list js object in IE 11.

        if (typeof(uploadFilePending) != 'undefined') {
            uploadFilePending = [];
        }
    }// resetInputFile
}// rdDownloadsAutoUpload


/**
 * Enable or diable buttons.
 * 
 * @param {boolean} enable Set to `true` to enable buttons, set to `false` to disable buttons. Default is `true`.
 * @returns {undefined}
 */
function rdDownloadsEnableDisableButtons(enable) {
    var $ = jQuery.noConflict();

    if (typeof(enable) === 'undefined') {
        enable = true;
    }

    if (enable === true) {
        // enable upload button.
        $('.rd-downloads-file-upload-button').removeClass('disabled');
        $('.rd-downloads-file-upload-button input[type="file"]').prop('disabled', false);
        // enable save button.
        $('.rd-downloads-save-form-button').prop('disabled', false);
    } else if (enable === false) {
        // disable upload button.
        $('.rd-downloads-file-upload-button').addClass('disabled');
        $('.rd-downloads-file-upload-button input[type="file"]').prop('disabled', true);
        // disable save button.
        $('.rd-downloads-save-form-button').prop('disabled', true);
    }
}// rdDownloadsEnableDisableButtons


/**
 * Prevent drag and drop file outside target.
 * 
 * It's required .rd-downlods-dropzone in html class to work.
 * 
 * @returns {undefined}
 */
function rdDownloadsPreventDropImageOutsideTarget() {
    var $ = jQuery.noConflict();

    $('html, body').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
        if (!$(this).hasClass('rd-downloads-dropzone')) {
            e.stopPropagation();
            e.preventDefault();
        }
    });
}// rdDownloadsPreventDropImageOutsideTarget


/**
 * Select local file.
 * 
 * @param {object} thisObj
 * @returns {undefined}
 */
function rdDownloadsSelectLocalFile(thisObj) {
    var $ = jQuery.noConflict();
    var Template = wp.template('selected-download-file-size');

    $('#rd-downloads-edit-form #download_type').val(0);
    $('#rd-downloads-edit-form #download_url').val(decodeURIComponent(thisObj.dataset.url));
    $('#rd-downloads-edit-form #download_related_path').val(thisObj.dataset.relatedpath);
    // show option force download because this user use upload.
    $('#rd-downloads-edit-form .rd-downloads-opt_force_download').removeClass('hidden');
    // display download file size.
    $('#rd-downloads-edit-form .download_size').html(Template({
        'size': thisObj.dataset.size,
        'url': decodeURIComponent(thisObj.dataset.url)
    }));
}// rdDownloadsSelectLocalFile


// on dom ready --------------------------------------------------------------------------------------------------------
(function ($) {
    // always reset the form to its beginning to prevent Firefox form cached when reload.
    $('#rd-downloads-edit-form')[0].reset();

    // activate ajax file browser.
    rdDownloadsAjaxFileBrowser();

    // prevent drop file outside target.
    rdDownloadsPreventDropImageOutsideTarget();
    // make input file auto upload.
    rdDownloadsAutoUpload();

    // get remote file size (including GitHub) on manual enter.
    rdDownloadsAjaxGetRemoteFileSize();

    // activate ajax save form.
    rdDownloadsAjaxSaveForm();

    if ($('#rd-downloads-edit-form #download_id').val() == '') {
        $('.rd-downloads-publish-data').addClass('hidden');
    }
})(jQuery);
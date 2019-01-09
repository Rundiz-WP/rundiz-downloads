/**
 * GitHub connect (OAuth) JS.
 */


class RdDownloadsGitHubOAuth {


    /**
     * Generate random string.
     *
     * @returns {String}
     */
    _randomString() {
        let text = "";
        let possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let totalChars = 20;

        for (var i = 0; i < totalChars; i++) {
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        }

        return text;
    }// _randomString


    /**
     * Check repository webhook that is it already added or not.
     *
     * @returns {undefined}
     */
    checkRepoWebhook() {
        let $ = jQuery.noConflict();

        $('.rddownloads_githubrepo_webhook_check').off('click');
        $('.rddownloads_githubrepo_webhook_check').on('click', function(e) {
            e.preventDefault();
            let thisTr = $(this).closest('tr');
            let thisLink = $(this);

            $(this).find('.rddownloads_icon-webhook-status').attr('class', 'rddownloads_icon-webhook-status').addClass('fas fa-spinner fa-pulse');

            $.ajax({
                'url': ajaxurl,
                'method': 'GET',
                'data': 'security=' + encodeURIComponent(RdDownloads.nonce) + '&action=RdDownloadsCheckGitHubWebhook&namewithowner=' + encodeURIComponent(thisTr.data('namewithowner')),
                'dataType': 'json'
            })
            .done(function(data, textStatus, jqXHR) {
                if (typeof(data) !== 'undefined' && typeof(data.responseJSON) !== 'undefined') {
                    var response = data.responseJSON;
                } else {
                    var response = data;
                }
                if (typeof(response) === 'undefined' || response === '' || response === null) {
                    response = {};
                }

                if (typeof(response) === 'object') {
                    if (typeof(response.foundWebhook) !== 'undefined' && response.foundWebhook === true) {
                        thisLink.html('<i class="rddownloads_icon-webhook-status fas fa-check"></i> ' + RdDownloads.txtExists);
                    } else {
                        thisLink.html('<i class="rddownloads_icon-webhook-status fas fa-times"></i> ' + RdDownloads.txtNotExists);
                    }
                }

                response = undefined;
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                thisLink.find('.rddownloads_icon-webhook-status').attr('class', 'rddownloads_icon-webhook-status').addClass('fas fa-question');
            })
            .always(function(data, textStatus, jqXHR) {
                if (typeof(data) !== 'undefined' && typeof(data.responseJSON) !== 'undefined') {
                    var response = data.responseJSON;
                } else {
                    var response = data;
                }
                if (typeof(response) === 'undefined' || response === '' || response === null) {
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
        })
    }// checkRepoWebhook


    /**
     * Enable or disable buttons.
     *
     * @param {boolean} isDisable Set to true to disable (default), set to false to enable.
     * @returns {undefined}
     */
    enableDisableButtons(isDisable = true) {
        let $ = jQuery.noConflict();

        if (isDisable !== true) {
            isDisable = false;
        }

        if (isDisable === true) {
            $('#rddownloads_regenerate_secret').prop('disabled', true);
            $('#rddownloads_forcesync_github_secret').prop('disabled', true);
        } else {
            $('#rddownloads_regenerate_secret').prop('disabled', false);
            $('#rddownloads_forcesync_github_secret').prop('disabled', false);
        }
    }// enableDisableButtons


    /**
     * Force synchronize secret with user's repositories.
     *
     * @returns {undefined}
     */
    forceSyncSecret() {
        let $ = jQuery.noConflict();
        let thisClass = this;

        $('#rddownloads_forcesync_github_secret').off('click');
        $('#rddownloads_forcesync_github_secret').on('click', function(e) {
            e.preventDefault();

            thisClass.enableDisableButtons();

            // display synchronizing message.
            let form_result_working = rdDownloadsGetNoticeElement('notice-warning', RdDownloads.txtSyncing);
            $('.rd-downloads-form-result-placeholder').html(form_result_working);
            $('html, body').animate({
                scrollTop: ($('.rd-downloads-form-result-placeholder').first().offset().top - 50)
            }, 500);
            rdDownloadsReActiveDismissable();

            $.ajax({
                'url': ajaxurl,
                'method': 'POST',
                'data': 'security=' + encodeURIComponent(RdDownloads.nonce) + '&action=RdDownloadsSyncGitHubSecretToAll',
                'dataType': 'json'
            })
            .always(function(data, textStatus, jqXHR) {
                if (typeof(data) !== 'undefined' && typeof(data.responseJSON) !== 'undefined') {
                    var response = data.responseJSON;
                } else {
                    var response = data;
                }
                if (typeof(response) === 'undefined' || response === '' || response === null) {
                    response = {};
                }

                // clear result placeholder before do next.
                $('.rd-downloads-form-result-placeholder').html('');

                if (typeof(response.form_result_class) !== 'undefined' && typeof(response.form_result_msg) !== 'undefined') {
                    var form_result_html = rdDownloadsGetNoticeElement(response.form_result_class, response.form_result_msg);

                    $('.rd-downloads-form-result-placeholder').html(form_result_html);
                    $('html, body').animate({
                        scrollTop: ($('.rd-downloads-form-result-placeholder').first().offset().top - 50)
                    }, 500);

                    rdDownloadsReActiveDismissable();
                }

                thisClass.enableDisableButtons(false);
            });
        })
    }// forceSyncSecret


    /**
     * Re-generate secret and ajax save.
     *
     * @returns {undefined}
     */
    regenerateSecret() {
        let $ = jQuery.noConflict();
        let thisClass = this;

        $('#rddownloads_regenerate_secret').off('click');
        $('#rddownloads_regenerate_secret').on('click', function(e) {
            e.preventDefault();

            let confirmVal = confirm(RdDownloads.txtAreYouSureRegenerateSecret);

            if (confirmVal === true) {
                let newSecret = RdDownloads.currentUserId + '_' + thisClass._randomString();
                $('#rddownloads_githubwebhook_secret').val(newSecret).attr('type', 'text');
                thisClass.enableDisableButtons();

                // display re-generating message.
                let form_result_working = rdDownloadsGetNoticeElement('notice-warning', RdDownloads.txtRegenerating);
                $('.rd-downloads-form-result-placeholder').html(form_result_working);
                $('html, body').animate({
                    scrollTop: ($('.rd-downloads-form-result-placeholder').first().offset().top - 50)
                }, 500);
                rdDownloadsReActiveDismissable();

                $.ajax({
                    'url': ajaxurl,
                    'method': 'POST',
                    'data': 'security=' + encodeURIComponent(RdDownloads.nonce) + '&action=RdDownloadsNewGitHubSecret&rddownloads_githubwebhook_secret=' + encodeURIComponent(newSecret),
                    'dataType': 'json'
                })
                .done(function(data, textStatus, jqXHR) {
                    if (typeof(data) !== 'undefined' && typeof(data.responseJSON) !== 'undefined') {
                        var response = data.responseJSON;
                    } else {
                        var response = data;
                    }
                    if (typeof(response) === 'undefined' || response === '' || response === null) {
                        response = {};
                    }

                    if (typeof(response) === 'object') {
                        if (typeof(response.githubSecret) !== 'undefined') {
                            if (response.githubSecret != newSecret) {
                                console.log('js generated secret: ' + newSecret + ', php generated secret: ' + response.githubSecret);
                            }
                            $('#rddownloads_githubwebhook_secret').val(response.githubSecret).attr('type', 'text');
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
                    if (typeof(response) === 'undefined' || response === '' || response === null) {
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

                    thisClass.enableDisableButtons(false);
                });
            }
        })
    }// regenerateSecret


    /**
     * Show/Hide secret field.
     *
     * @returns {undefined}
     */
    showHideSecret() {
        let $ = jQuery.noConflict();

        $('#rddownloads_showhide_secret').off('click');
        $('#rddownloads_showhide_secret').on('click', function(e) {
            e.preventDefault();

            let secretField = $('#rddownloads_githubwebhook_secret');
            if (secretField.attr('type') === 'password') {
                secretField.attr('type', 'text');
            } else {
                secretField.attr('type', 'password');
            }
        });
    }// showHideSecret


}


// on dom ready --------------------------------------------------------------------------------------------------------
(function ($) {
    let RdDownloadsGitHubOAuthClass = new RdDownloadsGitHubOAuth();

    // listen on click show/hide secret.
    RdDownloadsGitHubOAuthClass.showHideSecret();

    // listen on click re-generate secret.
    RdDownloadsGitHubOAuthClass.regenerateSecret();

    // listen on click force sync secret.
    RdDownloadsGitHubOAuthClass.forceSyncSecret();

    // listen on click to check webhook from each repository.
    RdDownloadsGitHubOAuthClass.checkRepoWebhook();
})(jQuery);
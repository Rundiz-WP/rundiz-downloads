/**
 * Admin dashboard widget functional.
 */


class RdDownloadsDashboardWidget {


    /**
     * Ajax get all downloads daily stat.
     *
     * @returns {unresolved}
     */
    ajaxGetAllDownloadsDailyStat() {
        let $ = jQuery.noConflict();

        let formData = 'security=' + encodeURIComponent(RdDownloads.nonce) + '&action=RdDownloadsDashboardWidgetAllDownloadsDailyStat';

        return $.ajax({
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

            console.log('ajax success get result for all downloads daily stat.');

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

            response = undefined;
        });
    }// ajaxGetAllDownloadsDailyStat


    /**
     * Ajax get top downloads.
     *
     * @returns {unresolved}
     */
    ajaxGetTopDownloads() {
        let $ = jQuery.noConflict();

        let filterScope = $('#rd-downloads_dashboard-widget_top-results-filter-select').val();
        let formData = 'security=' + encodeURIComponent(RdDownloads.nonce) + '&action=RdDownloadsDashboardWidgetTopDownloads&scope=' + encodeURIComponent(filterScope);

        $('#rd-downloads_dashboard-widget_top-results-filter-select').prop('disabled', true);

        return $.ajax({
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

            console.log('ajax success get result for top downloads.');

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

            $('#rd-downloads_dashboard-widget_top-results-filter-select').prop('disabled', false);

            response = undefined;
        });
    }// ajaxGetTopDownloads


    /**
     * Display all downloads daily stat.
     *
     * This method required chart.js to functional.
     *
     * @returns {undefined}
     */
    displayAllDownloadsDailyStat() {
        let $ = jQuery.noConflict();
        let deferred = $.Deferred();

        $.when(this.ajaxGetAllDownloadsDailyStat())
        .then(function(response) {
            let chartContext = $('#rd-downloads_dashboard-widget_all-downloads-daily-stat')[0].getContext('2d');

            let allDlChart = new Chart(chartContext, {
                type: 'line',
                data: {
                    labels: response.part_date_gmt,
                    datasets: [{
                        label: RdDownloads.txtTotalDownload,
                        data: response.part_total_success,
                        backgroundColor: 'rgba(0, 255, 0, 0.3)',
                        borderColor: 'rgba(0, 255, 0, 0.3)',
                        fill: false,
                        lineTension: 0
                    },
                    {
                        label: RdDownloads.txtTotalErrorDownload,
                        data: response.part_total_error,
                        backgroundColor: 'rgba(255, 0, 0, 0.3)',
                        borderColor: 'rgba(255, 0, 0, 0.3)',
                        fill: false,
                        lineTension: 0
                    },
                    {
                        label: RdDownloads.txtTotalAntibotFailed,
                        data: response.part_total_antibotfailed,
                        backgroundColor: 'rgba(255, 150, 0, 0.3)',
                        borderColor: 'rgba(255, 150, 0, 0.3)',
                        fill: false,
                        lineTension: 0
                    }]
                },
                options: {
                    responsive: true,
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: true
                    },
                    scales: {
                        xAxes: [{
                            type: 'time',
                            time: {
                                unit: 'day'
                            }
                        }]
                    }
                }
            });

            deferred.resolve();
        });

        return deferred.done();
    }// displayAllDownloadsDailyStat


    /**
     * Hide the list and display text getting data.
     *
     * @returns {undefined}
     */
    displayTextGettingDataTopDownloads() {
        let $ = jQuery.noConflict();

        $('.rd-downloads_dashboard-widget_top-results-list').addClass('hide hidden');

        let topDownloadResultsText = $('#rd-downloads_dashboard-widget_top-results-text');
        topDownloadResultsText.removeClass('hide hidden');
        topDownloadResultsText.html('<i class="fas fa-spinner fa-pulse fontawesome-icon icon-loading"></i> ' + RdDownloads.txtGettingData);
    }// displayTextGettingDataTopDownloads


    /**
     * Display top downloads.
     *
     * @returns {unresolved}
     */
    displayTopDownloads() {
        let $ = jQuery.noConflict();
        let deferred = $.Deferred();

        $.when(this.ajaxGetTopDownloads())
        .then(function(response) {
            let listTemplate = wp.template('rd-downloads-list-top-item');

            if (typeof(response) !== 'undefined' && typeof(response.total) !== 'undefined' && response.total <= 0) {
                $('#rd-downloads_dashboard-widget_top-results-text').html(RdDownloads.txtNoTopDownload);
            } else if (typeof(response) !== 'undefined' && typeof(response.results) !== 'undefined') {
                $('#rd-downloads_dashboard-widget_top-results-text').html('');
                $('#rd-downloads_dashboard-widget_top-results-text').addClass('hide hidden');
                $('#rd-downloads_dashboard-widget_top-results-list').html('');
                $('#rd-downloads_dashboard-widget_top-results-list').removeClass('hide hidden');
                $.each(response.results, function(index, item) {
                    $('#rd-downloads_dashboard-widget_top-results-list').append(listTemplate(item));
                });
            }

            deferred.resolve();
        });

        return deferred.done();
    }// displayTopDownloads


    /**
     * Listen on filter select box has changed and ajax get new data.
     *
     * @returns {undefined}
     */
    listenFilterChanged() {
        let $ = jQuery.noConflict();
        let deferred = $.Deferred();
        let thisClass = this;

        $('#rd-downloads_dashboard-widget_top-results-filter-select').off('change');
        $('#rd-downloads_dashboard-widget_top-results-filter-select').on('change', function(e) {
            e.preventDefault();

            console.log('on change filter scope. getting new data.');
            thisClass.displayTextGettingDataTopDownloads();
            thisClass.displayTopDownloads();
        });

        deferred.resolve();
        return deferred.done();
    }// listenFilterChanged


}// RdDownloadsDashboardWidget


// on dom ready --------------------------------------------------------------------------------------------------------
(function($) {
    let rdDownloadsDashboardWidget = new RdDownloadsDashboardWidget();

    // set text getting data while ajax getting other things.
    rdDownloadsDashboardWidget.displayTextGettingDataTopDownloads();

    // display all downloads daily stat.
    $.when(rdDownloadsDashboardWidget.displayAllDownloadsDailyStat())
    .then(function() {
        // display top downloads.
        return rdDownloadsDashboardWidget.displayTopDownloads();
    })
    .then(function() {
        // listen on filter select box on change.
        return rdDownloadsDashboardWidget.listenFilterChanged();
    })
    .done(function() {
        setTimeout(function() {
            console.log('Rundiz Downloads admin dashboard widget render completed.');
        }, 600);
    });
})(jQuery);
/**
 * Contain common functionals
 */


/**
 * Escape special chars in HTML.
 * 
 * @link https://stackoverflow.com/a/6234804/128761 Reference.
 * @param {string} string
 * @returns {string}
 */
function rdDownloadsEscapeHtml(string) {
    return string
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}// rdDownloadsEscapeHtml


/**
 * Extract host name.
 * 
 * For example: url is https://sub1.mydomain.com/path will return sub1.mydomain.com
 * 
 * This function required by `rdDownloadsExtractRootDomain()` function.
 * 
 * @link https://stackoverflow.com/a/23945027/128761 Reference.
 * @param {string} url
 * @returns {string}
 */
function rdDownloadsExtractHostname(url) {
    var hostname;
    //find & remove protocol (http, ftp, etc.) and get hostname

    if (url.indexOf("//") > -1) {
        hostname = url.split('/')[2];
    }
    else {
        hostname = url.split('/')[0];
    }

    //find & remove port number
    hostname = hostname.split(':')[0];
    //find & remove "?"
    hostname = hostname.split('?')[0];

    return hostname;
}// rdDownloadsExtractHostname


/**
 * Extract root domain.
 * 
 * For example: url is https://sub1.sub2.mydomain.com/path will return only mydomain.com
 * 
 * @link https://stackoverflow.com/a/23945027/128761 Reference.
 * @param {string} url
 * @returns {string}
 */
function rdDownloadsExtractRootDomain(url) {
    var domain = rdDownloadsExtractHostname(url),
        splitArr = domain.split('.'),
        arrLen = splitArr.length;

    //extracting the root domain here
    //if there is a subdomain 
    if (arrLen > 2) {
        domain = splitArr[arrLen - 2] + '.' + splitArr[arrLen - 1];
        //check to see if it's using a Country Code Top Level Domain (ccTLD) (i.e. ".me.uk")
        if (splitArr[arrLen - 2].length == 2 && splitArr[arrLen - 1].length == 2) {
            //this is using a ccTLD
            domain = splitArr[arrLen - 3] + '.' + domain;
        }
    }
    return domain;
}// rdDownloadsExtractRootDomain


/**
 * Generate notice element.
 * 
 * @link https://stackoverflow.com/a/36773193/128761 Reference for checking is html.
 * @param {string} notice_class
 * @param {string} notice_message
 * @returns {String}
 */
function rdDownloadsGetNoticeElement(notice_class, notice_message) {
    var isHTML = RegExp.prototype.test.bind(/(<([^>]+)>)/i);
    var output = '<div class="'+notice_class+' notice is-dismissible">';
    if (isHTML(notice_message)) {
        output += notice_message;
    } else {
        output += '<p><strong>'+notice_message+'</strong></p>'
    }
    output += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">'+RdDownloads.txtDismiss+'</span></button>'
        +'</div>';
    return output;
}// rdDownloadsGetNoticeElement


/**
 * Human readable file size.
 * 
 * @link https://stackoverflow.com/a/14919494/128761 Reference.
 * @param {int} byes
 * @param {bool} si Set to `false` for Kib, Mib, etc. Default is false.
 * @returns {String}
 */
function rdDownloadsHumanFileSize(bytes, si) {
    var thresh = si ? 1000 : 1024;
    if(Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
    var units = si
        ? ['KB','MB','GB','TB','PB','EB','ZB','YB']
        : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
    var u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while(Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(1)+' '+units[u];
}// rdDownloadsHumanFileSize


/**
 * Re-activate notice dismissable.
 * 
 * @returns {undefined}
 */
function rdDownloadsReActiveDismissable() {
    jQuery('.notice.is-dismissible').on('click', '.notice-dismiss', function(event){
        jQuery(this).closest('.notice').remove();
    });
}// rdDownloadsReActiveDismissable


/**
 * Un-Escape special chars in HTML.
 * 
 * @link https://stackoverflow.com/a/6234804/128761 Reference.
 * @param {string} string
 * @returns {string}
 */
function rdDownloadsUnEscapeHtml(string) {
    var doc = new DOMParser().parseFromString(string, "text/html");
    return doc.documentElement.textContent;
}// rdDownloadsUnEscapeHtml
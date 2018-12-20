/**
 * Add download button to classic editor (TinyMCE).
 */


tinymce.PluginManager.add('rddownloads_button', function (editor, url) {
	// Add a button that opens a window
	editor.addButton('rddownloads_button', {
		icon: 'icon-add-download fontawesome-icon fas fa-download',
		text: false,
		title: RdDownloads.txtAddADownload,
		onclick: function () {
			rdDownloadsClassicEditorOpenWindow(editor);
		}
	});

	// Adds a menu item to the tools menu
	editor.addMenuItem('rddownloads_button', {
		context: 'insert',
		icon: 'icon-add-download fontawesome-icon fas fa-download',
		text: RdDownloads.txtAddADownload,
		onclick: function () {
			rdDownloadsClassicEditorOpenWindow(editor);
		}
	});
});


function rdDownloadsClassicEditorOpenWindow(editor) {
    var dialog_width = (parseInt(jQuery(window).width())-20);
    var dialog_height = (parseInt(jQuery(window).height())-100);
    if (dialog_width >= 1000) {
        dialog_width = 1000;
    } else if (dialog_width <= 320) {
        dialog_width = 320;
    }
    if (dialog_height >= 700) {
        dialog_height = 700;
    } else if (dialog_height <= 300) {
        dialog_height = 200;
    }

    editor.windowManager.open({
        title: RdDownloads.txtAddADownload,
        height: dialog_height,
        width: dialog_width,
        body: [
            {
                type: 'container',
                html: rdDownloadsCreateDialogContent()
            },
        ],
        buttons: []
    });

    rdDownloadsListenSearchInput();
}// rdDownloadsClassicEditorOpenWindow


/**
 * Insert shortcode.
 * 
 * This function was called from insert button in downloadTinyMCEBrowser_v.php
 * 
 * @param {object} thisObj
 * @returns {Boolean} Always return false.
 */
function rdDownloadsInsertShortCodeButton(thisObj) {
    console.log(thisObj.dataset);
    var shortcodeString = '[rddownloads id="' + thisObj.dataset.download_id + '"]';

    tinymce.activeEditor.execCommand('mceInsertContent', false, shortcodeString);
    top.tinymce.activeEditor.windowManager.close();

    return false;
}// rdDownloadsInsertShortCodeButton
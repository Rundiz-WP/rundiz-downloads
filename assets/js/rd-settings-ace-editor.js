

// on dom ready --------------------------------------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    let editor = [];
    let textarea_editor = [];
    let textarea_id = [];

    document.querySelectorAll('.ace-editor')?.forEach((item, index) => {
        editor[index] = ace.edit(item);

        const editor_mode = item.dataset.editor_mode;
        textarea_id[index] = item.dataset.target_textarea;
        textarea_editor[index] = document.querySelector(textarea_id[index]);

        textarea_editor[index].classList.add('hidden');

        editor[index].setOptions({
            maxLines: 'Infinity',
            mode: 'ace/mode/' + editor_mode,
            theme: 'ace/theme/monokai'
        })

        editor[index].getSession().setValue(textarea_editor[index].value);
        editor[index].getSession().on('change', function (e) {
            console.log('>' + textarea_id[index] + ' had changed');
            textarea_editor[index].value = editor[index].getSession().getValue();
        });
    });
});
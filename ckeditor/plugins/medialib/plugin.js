CKEDITOR.plugins.add( 'medialib', {
    icons: 'medialib',
    init: function( editor ) {

	var editorx = editor.name;
			editor.ui.addButton('ck_mlib_button_'+editorx, { // add new button and bind our command
			label: "Insert Media",
			command: 'medialib',
			toolbar: 'insert',
			icon: CKEDITOR.basePath+'plugins/medialib/icons/medialib.png'
			});
	
	setTimeout(function() {
    init_ckeditor_medialib(editorx);
	}, 1000);

        
    }
});
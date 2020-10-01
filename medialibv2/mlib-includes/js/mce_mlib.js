tinymce.PluginManager.add('mce_mlib', function(editor, url) {
	var tinyedit_id = editor.id;
    editor.addButton('mce_mlib', {
			text: 'Insert Media',
			icon: false,
			id: 'mediabtn_'+tinyedit_id,
			icon : 'image',
            onclick: function() {
			var newid = '#mediabtn_'+tinyedit_id;
			if($(newid).attr('mboxmce_init')===undefined){
			$(newid).attr('mboxmce_init', tinyedit_id);
			$(newid).mlibready({allowed:'jpg,png,gif,jpeg,txt,zip,rar,doc,docx,ppt,pptx,xls,xlsx,csv,tar,gz', mcename:tinyedit_id, returnas:'all'});
			$(newid).trigger('click');
			}
            }
    });

    // Adds a menu item
    editor.addMenuItem('mce_mlib', {
        text: 'Insert Media',
        context: 'file',
		icon : 'image',
		id: 'mediabtn_'+tinyedit_id,
        onclick: function() {
			var newid = '#mediabtn_'+tinyedit_id;
			if($(newid).attr('mboxmce_init')===undefined){
			$(newid).attr('mboxmce_init', tinyedit_id);
			$(newid).mlibready({allowed:'jpg,png,gif,jpeg,txt,zip,rar,doc,docx,ppt,pptx,xls,xlsx,csv,tar,gz', mcename:tinyedit_id, returnas:'all'});
			$(newid).trigger('click');
			}
        }
    });
});
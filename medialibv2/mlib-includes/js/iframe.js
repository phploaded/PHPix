$( document ).ready(function() {

if(mlib_parent_init=='1'){
window.top.mlib_adjust_iframe();
setTimeout("mlib_reload_frame()", 500);
}

$('body').on('click', '#addmoreupload', function(){
$(this).closest('.mlib-extra-upload').after('<div class="mlib-extra-upload"><input type="file" name="file[]" /> <input type="button" class="mlib_delete_upload" value="x Remove" /></div>');
window.top.mlib_adjust_iframe();
});


$('body').on('click', ".mlib_delete_upload", function(){
$(this).parent().remove();
window.top.mlib_adjust_iframe();
});

});


function mlib_reload_frame(){
window.top.mlib_thumbs_after_upload();
}
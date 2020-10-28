/* mlib global js vars */
mlib_vars_tab1_text = 'Upload Files';
mlib_vars_h1_text = 'Upload Files';
mlib_vars_tab2_text = 'Insert from Existing';
mlib_vars_h2_text = 'Insert Media from Existing Files';
mlib_vars_tab3_text = 'Insert from URL';
mlib_vars_h3_text = 'Embed from URL or web address';
mlib_vars_insert_button_text = 'Insert';
mlib_vars_manage_spots_text = 'Manage photo spots';
mlib_vars_select_multiple = 'no';

function mlib_refresh(){
var ipp = $('#mlib-lightbox').attr('mlib-ipp');
var xpage = $('#mlib-lightbox').attr('mlib-page');
mlib_load_gallery_data_advanced(xpage, ipp);
}


function mlib_uploaded_preview(xthumb){
$('.mlib-uploaded-preview').prepend('<div class="mlib-uploaded-thumb" style="background-image:url(\''+main_domain+'thumb/'+xthumb+'\')"></div>');
}





$(document).ready(function(){

$( window ).resize(function() {
mlib_adjust_iframe();
});

$('body').on('change', '.mlib_ipp, #mlib-sortby', function(){
var ipp = $('#mlib-lightbox .mlib_ipp').val();
var xpage = $('#mlib-lightbox').attr('mlib-page');
mlib_load_gallery_data_advanced(xpage, ipp);
});




$('body').on('click', '.mlib-save-changes', function(e){
e.preventDefault();
$.post( mlib_domain+"mlib.php", $('form.mlib-single-edit').serialize(), function( data ) {
var xjson = JSON.parse(data);
if(xjson.mlibid == '' || xjson.mlibid === undefined){
alert('Some error occured, details could not be updated!!'+data);
} else {
$('.mlib-single-edit [name="title"]').val(xjson.title);
$('.mlib-single-edit [name="caption"]').val(xjson.caption);
$('.mlib-single-edit [name="tags"]').val(xjson.tags);
$('.mlib-single-edit [name="emails"]').val(xjson.emails);

// update in actual thumbnail
$('.mlib-selected-thumb[mlib-id="'+xjson.mlibid+'"]').attr('mlib-title', xjson.title);
$('.mlib-selected-thumb[mlib-id="'+xjson.mlibid+'"]').attr('mlib-caption', xjson.caption);
$('.mlib-selected-thumb[mlib-id="'+xjson.mlibid+'"]').attr('mlib-tags', xjson.tags);
$('.mlib-selected-thumb[mlib-id="'+xjson.mlibid+'"]').attr('mlib-emails', xjson.emails);
$('.mlib-selected-thumb[mlib-id="'+xjson.mlibid+'"]').attr('mlib-access', xjson.access);

alert('Updated successfully!');
}
});
});


$('body').on('click', '.mlib-save-type', function(e){
e.preventDefault();
$.post( mlib_domain+"mlib.php", $(this).closest('form.mlib-edit-method').serialize(), function( data ) {
alert('All changes were saved!!');
});
});


$('body').on('click', '#mlib_chooser .mlib-import-method', function(){
var xhtml = '';
var template = $(this).find('.mlib-import-data-raw').html();

$(".mlib-selected-thumb").each(function(i) {
var xtitle = $(this).attr('mlib-title');
var xcaption = $(this).attr('mlib-caption');
var xtype = $(this).attr('mlib-type');
var xid = $(this).attr('mlib-id');
var xurl = $(this).attr('mlib-url');
var xthumb = $(this).attr('mlib-thumb');
var bytesize = $(this).attr('mlib-size');
var xtags = $(this).attr('mlib-tags');
var fullsize = mlib_size(parseInt(bytesize));
var thtml = template;

thtml = mlib_replace_all(thtml, '%%title%%', xtitle);
thtml = mlib_replace_all(thtml, '%%caption%%', xcaption);
thtml = mlib_replace_all(thtml, '%%type%%', xtype);
thtml = mlib_replace_all(thtml, '%%id%%', xid);
thtml = mlib_replace_all(thtml, '%%url%%', xurl);
thtml = mlib_replace_all(thtml, '%%thumb%%', xthumb);
thtml = mlib_replace_all(thtml, '%%bytesize%%', bytesize);
thtml = mlib_replace_all(thtml, '%%fullsize%%', fullsize);
thtml = mlib_replace_all(thtml, '%%tags%%', xtags);
thtml = mlib_replace_all(thtml, '%%domain%%', main_domain);

xhtml = xhtml+thtml;

});
var rid = $('#mlib-lightbox').attr('mlib-return-to');
var y = $('#mlib-lightbox').attr('mlib-function');
var is_element_input = $(rid).is("input");
if(is_element_input){
$(rid).val(xhtml);
} else {
$(rid).html(xhtml);
}


// additional block for tinyMCE
var mcename = $('#mlib-lightbox').attr('mlib-tinymce');
if(mcename != '' && mcename !== undefined ){
//tinymce.activeEditor.execCommand('mceInsertContent', false, xhtml);
tinyMCE.get(mcename).execCommand('mceInsertContent', false, xhtml);
}


// additional block for CKEditor
var ckename = $('#mlib-lightbox').attr('mlib-ckename');
if(ckename!=''){
CKEDITOR.instances[ckename].insertHtml(xhtml);
}


$('.mlib-close').trigger('click');

if(y!='null'){
window[y](xhtml, rid);
}

});

$('body').on('click', '.close-mlib-chooser', function(){
$('#mlib_chooser').hide();
});


$('body').on('click', '.mlib-insert-button', function(){
var allowed_types_string = mlib_replace_all($('#mlib-lightbox').attr('mlib-allowed'), ' ', '');
var allowed_types=allowed_types_string.split(',');
var selectedx = $('.mlib-selected-thumb').length;
var maxx = parseInt($('#mlib-lightbox').attr('mlib-max-selection'));
var minx = parseInt($('#mlib-lightbox').attr('mlib-min-selection'));
if(selectedx>maxx){
alert('You can select maximum '+maxx+' files.');
return false;
}

if(selectedx<minx){
alert('You must select at least '+minx+' files.');
return false;
}

var allowed = 1;

$( ".mlib-selected-thumb" ).each(function( index ) {
var xtype = $(this).attr('mlib-type');
if(jQuery.inArray( xtype, allowed_types )==-1){allowed=0;}
});

if(allowed==0){
alert('You are allowed to select '+allowed_types_string+' files only.');
return false;
}

var x = $('#mlib-lightbox').attr('mlib-return-as');

if(x!=''){
if($('#mlib_chooser .mlib-import-method[mlib-id="'+x+'"]').length==0){
$('#mlib_chooser').show();
} else {
$('#mlib_chooser .mlib-import-method[mlib-id="'+x+'"]').trigger('click');
}
}

});

// save new import option
$('body').on('click', '#mlib-create-button', function(){
$.post(mlib_domain+"mlib.php", $('#newoption').serialize(), function(data) {
alert( data );
// clear import option cache
load_import_options();
});
});

// closing the box
$('body').on('click', '.mlib-close', function(){

// these two lines should be removed in other projects
var returnID = $('#mlib-lightbox').attr('mlib-return-to');
album_after_function('', returnID);

$('#mlib-lightbox').remove();
});

// open extra options
$('body').on('click', '.mlib-new-option-button', function(){
$('.mlib-new-option').slideDown("slow");
$('.mlib-new-option-button').hide();
});

$('body').on('click', '.mlib-new-option .mlib-danger', function(){
$('.mlib-new-option').slideUp("slow", function(){
$('.mlib-new-option-button').show();
});
});

// selecting all thumbnails by pressing Ctrl+A
$('body').on('click keyup', '.mlib-delete-all', function (){
if(confirm('All selected files will be deleted. This cannot be undone. Are you sure to continue?')){
mlib_delete_selected();
}
});


// selecting all thumbnails by pressing Ctrl+A
$('body').on('keyup', function (e){

if($('#mlib-media-li.mlib-li-active').length==1){
if(e.shiftKey && e.keyCode == 65){
$(".mlib-thumbs:last").trigger('mousedown');
$( ".mlib-thumbs" ).each(function() {
if(!$(this).hasClass('mlib-selected-thumb')){
$(this).addClass('mlib-selected-thumb');
}
});
var tot = $('.mlib-selected-thumb').length;
$('.mlib-how-many').html('<b style="color:red;">'+tot+' items selected.</b> <span class="hidden-text">Hold CTRL then click to select multiple items OR Press SHIFT + A to select all.</span><div class="mlib-how-many-text"><div style="float: right; margin: 10px 0px;" class="mlib-delete-all">delete</div> <div style="float: right; margin: 10px;" class="mlib-button mlib-insert-button">Insert</div></div>');
//$('.mlib-thumbs').addClass('mlib-selected-thumb');
}
}

});

$('body').on('mousedown', '.mlib-thumbs', function (e){
ctrlKeyHeld = e.ctrlKey;
if($('#multi-select-thumbs').prop('checked')==true || ctrlKeyHeld==true){
if($(this).find('input[checked="checked"]').length!=0){
$(this).removeClass('mlib-selected-thumb');
$(this).find('[type="checkbox"]').removeAttr('checked');
} else {
$(this).addClass('mlib-selected-thumb');
$(this).find('[type="checkbox"]').attr('checked', 'checked');
}
} else {
$(this).closest('.mlib-display-canvas').find('.mlib-thumbs').removeClass('mlib-selected-thumb');
$(this).closest('.mlib-display-canvas').find('[type="checkbox"]').removeAttr('checked');
$(this).addClass('mlib-selected-thumb');
$(this).find('[type="checkbox"]').attr('checked', 'checked');
}

var tot = $('.mlib-selected-thumb').length;

if(mlib_vars_select_multiple=='no'){var mchk_text = '';} else {var mchk_text = 'checked="checked"';}
$('.mlib-how-many').html('<span class="hidden-text"> &nbsp; <input onclick="mlib_mchk_text_val()" type="checkbox" '+mchk_text+' id="multi-select-thumbs" /> select multiple</span><b class="mlib-sel-text">'+tot+' items selected.</b><div class="mlib-how-many-text"><div style="float: right; margin: 10px 0px;" class="mlib-delete-all">delete</div> <div style="float: right; margin: 10px;" class="mlib-button mlib-insert-button">'+mlib_vars_insert_button_text+'</div></div>');

var mhtml = '<form action="" method="post" name="mlib-single-form" class="mlib-single-edit">\
<div style="text-align:center;padding-top:10px;"><a href="javascript:void(0)" class="btn btn-xs btn-info mlib-album-cover">SET COVER</a> <a href="javascript:void(0)" class="btn btn-xs btn-warning" onclick="mlib_change_album()">MOVE TO</a> <a href="javascript:void(0)" class="btn btn-xs btn-success" onclick="mlib_rotate_photo()">ROTATE</a></div>\
<label><span>'+mlib_size($(this).attr('mlib-size'), 2)+', '+$(this).attr('mlib-type')+' file</span></label>\
<label><span>Uploaded on '+$(this).attr('mlib-time')+'</span></label>\
<label>\
<span>Privacy &nbsp; - &nbsp; <input type="radio" onchange="mlib_switch_access(this)" id="optionpublic" name="access" value="public"> Public &nbsp; \
<input type="radio" onchange="mlib_switch_access(this)" id="optionprivate" name="access" value="private"> Private</span>\
</label>\
<select name="maillist[]" multiple id="maillist"></select>\
\
<label>\
<span>File URL</span>\
<input type="text" readonly="readonly" value="'+main_domain+'full/'+$(this).attr('mlib-url')+'">\
<input type="hidden" name="mlibid" value="'+$(this).attr('mlib-id')+'">\
<input type="hidden" name="func" value="mlib_single_edit">\
</label>\
<label>\
<span>Title</span>\
<input type="text" name="title" value="'+$(this).attr('mlib-title')+'">\
</label>\
\
<label>\
<span>Caption</span>\
<textarea name="caption" rows="3">'+$(this).attr('mlib-caption')+'</textarea>\
</label>\
\
<label>\
<span>Tags - <a target="_blank" href="'+main_domain+''+albumFILE+'?aid='+$('.mlib-main').attr('id')+'&pic='+$(this).attr('mlib-url')+'">'+mlib_vars_manage_spots_text+'</a></span>\
<textarea name="tags" rows="3">'+$(this).attr('mlib-tags')+'</textarea>\
</label>\
\
<input type="submit" class="mlib-save-changes" value="Save Changes" name="dfdf">\
</form>';

$('.mlib-item-properties').html(mhtml);
$('#option'+$(this).attr('mlib-access')).prop('checked', true);
if($(this).attr('mlib-access') == 'private'){
$('.mlib-single-edit .ms-options-wrap').show();
} else {
$('.mlib-single-edit .ms-options-wrap').hide();
}
mlib_photo_access($(this).attr('mlib-id'));
});



$('body').on('click', '.mlib-left li', function(){
var xid = $(this).attr('id');
var cid = xid.replace('-li', '-tab');
$('.mlib-left li').removeClass('mlib-li-active');
$(this).addClass('mlib-li-active');
$('.mlib-data').hide();
$('#'+cid).show();
mlib_menu_toggle();
});

});


function mlib_mchk_text_val(){
if($('#multi-select-thumbs').prop("checked")==true){
mlib_vars_select_multiple = 'yes';
} else {
mlib_vars_select_multiple = 'no';
}

}

function mlib_strto_option(xstr){

var xarr = xstr.split(',');
var out = '';
for(var i=0;i<xarr.length;i++){
var eid = $.trim(xarr[i]);
out = out + '<option value="'+eid+'">'+eid+'</option>';
}

return out;
}

/* php rotates counterclockwise, but css does clockwise */
function mlib_rotate_photo(){
var xid = $('.mlib-single-edit [name="mlibid"]').val();
var thumb = $('[mlib-id="'+xid+'"]').attr('mlib-thumb');
var xhtml = '\
<p style="color:red;"><b>WARNING!</b> Rotating this image will also remove the camera and other related information from photo. It is always good to delete the incorrect photo and reupload the correctly rotated photo.</p><br />\
<p>Once done, viewers need to clear cache to see the changes.</p><br /><h5>Choose degress:</h5>\
<div class="rotate-canvas"><img id="rotate-preview-img" src="'+main_domain+'thumb/'+thumb+'"></div>\
<div class="rotate-form"><br />\
<input onchange="mlib_rotate_preview(this.value)" checked type="radio" name="deg" value="0"> 0 degrees<br />\
<input onchange="mlib_rotate_preview(this.value)" type="radio" name="deg" value="90"> 90 degrees<br />\
<input onchange="mlib_rotate_preview(this.value)" type="radio" name="deg" value="180"> 180 degrees<br />\
<input onchange="mlib_rotate_preview(this.value)" type="radio" name="deg" value="270"> 270 degrees<br />\
</div>\
<div style="clear:both;"></div>\
<div class="mlib-rotate-result"></div>'; 

var xfooter ='<div class="phpl-alert-btn-danger phpl-alert-close">Close</div><div onclick="mlib_apply_rotate()" class="phpl-alert-btn-success">Apply</div>';

phpl_alert(xhtml, 'Rotate photo by degress', xfooter);
}

function mlib_apply_rotate(){
var xdeg = $('.rotate-form [name="deg"]:checked').val();
var xid = $('.mlib-single-edit [name="mlibid"]').val();
var thumb = $('[mlib-id="'+xid+'"]').attr('mlib-thumb');
var url = $('[mlib-id="'+xid+'"]').attr('mlib-url');
$('.phpl-alert-box').addClass('phpl-alert-loading');

$.post( mlib_domain+"mlib.php", {func:'mlib_photo_rotate', deg:xdeg, id:xid, u:url}, function(data) {
$('.phpl-alert-box').removeClass('phpl-alert-loading');
var xt = new Date().getTime();
phpl_close_alert();
phpl_alert('Image rotation successful!');

mlib_load_gallery_data_auto();
});

}


function mlib_load_gallery_data_auto(){
var xpage = $('#mlib-lightbox').attr('mlib-page');
var ipp = $('#mlib-lightbox').attr('mlib-ipp');
mlib_load_gallery_data_advanced(xpage, ipp);
}

function mlib_rotate_preview(xdeg){
$('#rotate-preview-img').css('transform', 'rotate('+xdeg+'deg)');
}

function mlib_photo_access(pid){
$.post( mlib_domain+"mlib.php", {func:'mlib_photo_access', aid:pid}, function( data ) {
$('#maillist').html(data);
$('#maillist').multiselect({
    columns: 1,
    placeholder: 'Select users',
    search: true,
    selectAll: true
});
});
}


function mlib_switch_access(xthis){

var xval = $(xthis).val();

if(xval=='public'){
$('#emails').hide();
} else {
$('#emails').show();
}

}


function mlib_change_album(){
var xhtml = '<select class="form-control" name="album-id" id="album-id">'+$('#album-ids').html()+'</select><br><p style="color:red;"><b>NOTE:</b>All selected files will be moved to the above selected folder!</p>';
phpl_alert(xhtml, 'Choose new album...', '<div class="phpl-alert-btn-danger phpl-alert-close">Close</div><div onclick="mlib_move_ajax()" class="phpl-alert-btn-success">Move</div>');
}

function mlib_url_upload(){
var url = $('.mlib-urls textarea[name="urls"]').val();

if(url.length<4){
alert('Please enter a URL');
$('.mlib-urls textarea[name="urls"]').trigger('focus');
} else {
$('#form-url-upload').hide();
$( ".mlib-ajax-result" ).html( '<h3>Please wait and do not close this. Files are being processed.</h3>' );
var fid = $('.mlib-main').attr('id');
$.post( mlib_domain+"mlib.php?fid="+fid, $('#form-url-upload').serialize(), function( data ) {
$( ".mlib-ajax-result" ).html( data );
$('.mlib-urls textarea[name="urls"]').val('');
$('#form-url-upload').show();
});

}
return false;
}



function mlib_load_gallery_data(){
mlib_load_gallery_data_advanced(1, 30);
}

function mlib_load_gallery_data_advanced(xpage, xipp){
var mlib_sortby = $('#mlib-sortby').val();
var fid = $('.mlib-main').attr('id');
$.post( mlib_domain+"mlib.php?fid="+fid, {func:'load_thumbs', page:xpage, ipp:xipp, sort:mlib_sortby}, function(data){
mlib_create_display(data);
});
}

function mlib_create_display(data){
var xdata = jQuery.parseJSON(data);
var xstr = '';
for(var i=0;i<parseInt(xdata.total);++i){

xstr = xstr+'<div mlib-access="'+xdata[i].access+'" mlib-size="'+xdata[i].size+'" mlib-id="'+xdata[i].id+'" mlib-type="'+xdata[i].type+'" mlib-time="'+xdata[i].newtime+'" mlib-title="'+xdata[i].title+'" mlib-caption="'+xdata[i].caption+'" mlib-url="'+xdata[i].url+'" mlib-thumb="'+xdata[i].thumb+'"  mlib-tags="'+xdata[i].tags+'" class="mlib-thumbs" style="background-image:url(\''+main_domain+'thumb/'+xdata[i].thumb+'\')">\
<input type="checkbox" name="img['+xdata[i].id+']">\
<div class="mlib-checkbox"></div></div>';

}

var load_more = '<div class="mlib-load-more"><div class="mlib-load-ipp"><select name="mlib_sortby" id="mlib-sortby"><option disabled>-- SORT BY --</option><option value="time-DESC">Uploaded (DESC)</option><option value="title-ASC">Title (ASC)</option><option value="size-DESC">Filesize (DESC)</option><option value="access-ASC">Private First</option></select> <select name="mlib_ipp" class="mlib_ipp"><option disabled> -- Items per page -- </option><option>30</option><option>50</option><option>100</option><option>200</option><option>500</option><option>1000</option></select></div><div class="mlib-linked-pages"></div></div>';

if(xdata.total==xdata.ipp){
var load_more_bottom = load_more;
} else {var load_more_bottom='';}

if(xstr==''){
xstr = '<p style="padding:0 10px;"><b>Sorry!</b> There are no images to show.</p>';
}

$( ".mlib-display-canvas" ).html( load_more+xstr+load_more_bottom );
$('.mlib-load-more .mlib_ipp').val(xdata.ipp);
$('.mlib-load-more #mlib-sortby').val(xdata.sort);
//$( ".mlib-display-canvas" ).prepend( load_more );
//$( ".mlib-display-canvas" ).after( load_more );
var mlib_ipp = $('.mlib-load-more .mlib_ipp').val();
$('.mlib-load-more .mlib-linked-pages').pagination({
items: xdata.gtotal,
itemsOnPage: mlib_ipp,
hrefTextPrefix:'',
hrefTextSuffix:'',
currentPage: xdata.page,
cssStyle: 'light-theme',
onPageClick : function(pageNumber, event){mlib_navigate_page(pageNumber, event)}
});
$('#mlib-lightbox').attr('mlib-ipp',xdata.ipp);
$('#mlib-lightbox').attr('mlib-page',xdata.page);
}

function mlib_navigate_page(currpage, e){
e.preventDefault();
var mlib_ipp = $('.mlib-load-more .mlib_ipp').val();
mlib_load_gallery_data_advanced(currpage, mlib_ipp);
}


function mlib_delete_selected(){
var mhtml = '';
// create a hidden for containing all selected images
$(".mlib-selected-thumb").each(function(index){
var mid = $(this).attr('mlib-id');
mhtml = mhtml+'<input type="hidden" name="mlibid[]" value="'+mid+'" />';
});


var xhtml = '<form name="mlibdelform" id="mlibdelform" action="" method="post">\
<input type="hidden" name="func" value="mlib_delete_items" />'+mhtml+'</form>';
$('body').append(xhtml);

$.post( mlib_domain+"mlib.php", $('form#mlibdelform').serialize(), function( data ) {

alert(data);

$(".mlib-selected-thumb").addClass('mlib-danger').fadeOut("slow", function(){
$(this).remove();
$('#mlibdelform').remove();
mlib_load_gallery_data_auto();
});

});
}


function mlib_move_ajaxx(){
$.post( mlib_domain+"mlib.php", {func:'mlib_change_album', xto:$('#album-id').val(), xfrom:$('.mlib-main').attr('id'), xphoto:$('.mlib-single-edit input[name="mlibid"]').val()}, function( data ) {
phpl_close_alert();
mlib_load_gallery_data_auto();
});
}


function mlib_move_ajax(){
var mhtml = '';
// create a hidden for containing all selected images
$(".mlib-selected-thumb").each(function(index){
var mid = $(this).attr('mlib-id');
mhtml = mhtml+'<input type="hidden" name="mlibid[]" value="'+mid+'" />';
});
// $('.mlib-single-edit input[name="mlibid"]').val()
var xhtml = '<form name="mlibmovform" id="mlibmovform" action="" method="post">\
<input type="hidden" name="func" value="mlib_move_items" />\
<input type="hidden" name="xto" value="'+$('#album-id').val()+'" />\
<input type="hidden" name="xfrom" value="'+$('.mlib-main').attr('id')+'" />\
'+mhtml+'</form>';
$('body').append(xhtml);

$.post( mlib_domain+"mlib.php", $('form#mlibmovform').serialize(), function( data ) {

$(".mlib-selected-thumb").addClass('mlib-danger').fadeOut("slow", function(){
$(this).remove();
$('#mlibmovform').remove();
mlib_load_gallery_data_auto();
phpl_close_alert();
});

});
}



function load_import_options(){
var xhtml = '<form name="0-saveform" method="post" action="" mlib-id="0" class="mlib-edit-method"><input type="hidden" name="mlibtypeid" value="0" /><b>id = 0</b><input readonly="readonly" type="text" name="title" value="Full URL Only" /><textarea readonly="readonly" name="content" rows="5">%%url%%</textarea><input type="hidden" name="func" value="mlib_save_type">Primary Option, cannot be saved or edited</form>';
var yhtml = '<div mlib-id="0" class="mlib-import-method"><h3>Full URL only</h3><div class="mlib-import-data">%%url%%</div><div class="mlib-import-data-raw">%%url%%</div></div>';
$.post( mlib_domain+"mlib.php", {func:'mlib_get_import_methods'} , function(datax) {
data = jQuery.parseJSON(datax);
$('.mlib-new-option .mlib-danger').trigger('click');
for(var i=0;i<data.total;++i){
xhtml = xhtml+'<form name="'+data[i].id+'-saveform" method="post" action="" mlib-id="'+data[i].id+'" class="mlib-edit-method"><input type="hidden" name="mlibtypeid" value="'+data[i].id+'" /><b>id = '+data[i].id+'</b><input type="text" name="title" value="'+data[i].title+'" /><textarea name="content" rows="5">'+data[i].content+'</textarea><input type="hidden" name="func" value="mlib_save_type"><input type="submit" name="save" class="mlib-button mlib-save-type" value="Save Changes" /> <input type="reset" name="reset" class="mlib-button mlib-danger" value="Delete Option" /></form>';
yhtml = yhtml+'<div mlib-id="'+data[i].id+'" class="mlib-import-method"><h3>'+data[i].title+'</h3><div class="mlib-import-data">'+data[i].contentx+'</div><div class="mlib-import-data-raw">'+data[i].content+'</div></div>';
}
$('#mlib-import-methods').html('<div style="clear:both;"></div>'+xhtml);
$('#mlib_chooser_data').html(yhtml);
});
}

function launch_mlib_box(allowed, returnto, returnas, maxselect, minselect, admin, runfunction, xmaxFilesize, xparallelUploads, xthumbnailWidth, xthumbnailHeight, folderID, mcename, ckename){

if(admin==1){
var admintab = '<li id="mlib-import-li">Import Options</li>';
var adminbox = '<div class="mlib-top"><div class="mlib-head">Manage Import Options</div><div class="mlib-close">X</div></div>\
<div class="mlib-button mlib-new-option-button" style="float:left;margin:10px;">+ Add New Import Option</div>\
<fieldset class="mlib-new-option">\
<legend>Add New import option</legend>\
<form name="newoption" id="newoption" action="" method="post">\
<input type="text" name="name" placeholder="Scheme Title">\
<textarea name="data" rows="8" placeholder="Scheme content for single image. All images will be imported as this scheme when selected."></textarea>\
<input type="button" id="mlib-create-button" class="mlib-button" value="Create" />\
 <input type="reset" class="mlib-button mlib-danger" value="Cancel" />\
<input type="hidden" name="func" value="mlib_create_import_method" />\
</form></fieldset>\
<div id="mlib-import-methods"></div>';
} else {
var admintab = '';
var adminbox = '';
}
var xhtml = '<style type="text/css">.dropzone .dz-preview .dz-error-message{right:-10px !important; left:-10px !important; width:auto !important;} .dropzone .dz-preview .dz-image{border-radius: 6px !important;height:'+xthumbnailHeight+'px !important;width:'+xthumbnailWidth+'px !important;}</style><div mlib-function="'+runfunction+'" mlib-min-selection="'+minselect+'" mlib-max-selection="'+maxselect+'" mlib-allowed="'+allowed+'" mlib-return-to="'+returnto+'" mlib-return-as="'+returnas+'" mlib-tinymce="'+mcename+'" mlib-ckename="'+ckename+'" id="mlib-lightbox">\
<div class="mlib-bg"></div>\
<div id="'+folderID+'" class="mlib-main">\
<ul class="mlib-left">\
<li class="mlib-li-active" id="mlib-upload-li">'+mlib_vars_tab1_text+'</li>\
<li id="mlib-media-li">'+mlib_vars_tab2_text+'</li>\
'+admintab+'\
<li id="mlib-url-li">'+mlib_vars_tab3_text+'</li>\
</ul><div class="mlib-right"><div class="mlib-contents"><div class="mlib-data" style="display:block;" id="mlib-upload-tab">\
<div class="mlib-top"><div class="mlib-head"><div onclick="mlib_menu_toggle()" class="mlib-menu-toggle"></div><div class="mlib-top-text">'+mlib_vars_h1_text+'</div></div><div class="mlib-close">X</div></div>\
<form action="mlib-upload.php" class="dropzone">\
<div class="fallback"><div class="mlib_fallback_iframe"><iframe id="mlib_fallback_iframe" src="'+mlib_domain+'mlib-iframe.php" onload="javascript:mlib_adjust_iframe()" frameborder="0" width="100%"></iframe></div>\
</div></form></div><div class="mlib-data" id="mlib-media-tab">\
<div class="mlib-top"><div class="mlib-head"><div onclick="mlib_menu_toggle()" class="mlib-menu-toggle"></div><div class="mlib-top-text">'+mlib_vars_h2_text+'</div><div onclick="mlib_toggle_fileinfo()" class="mlib-toggle-fileinfo"></div></div><div class="mlib-close">X</div></div>\
<div class="mlib-bottom"><div class="mlib-how-many"><span class="hidden-text">Hold CTRL then click to select multiple items OR Press SHIFT + A to select all.</span></div>\
</div><div class="mlib-display-canvas">\
<form name="myform" action="" method="post">\
<div id="mlib-navi1" class="mlib-navi"></div>\
<div id="mlib-navi2" class="mlib-navi"></div>\
</form></div><div class="mlib-item-properties"></div>\
</div><div class="mlib-data" id="mlib-import-tab">'+adminbox+'</div>\
<div class="mlib-data" id="mlib-url-tab">\
<div class="mlib-top"><div class="mlib-head"><div onclick="mlib_menu_toggle()" class="mlib-menu-toggle"></div><div class="mlib-top-text">'+mlib_vars_h3_text+'</div></div><div class="mlib-close">X</div></div>\
<div class="mlib-urls">\
<form onsubmit="return mlib_url_upload()" action="mlib.php" id="form-url-upload" name="xyz" method="post">\
<p>Enter the URLs to download. You can enter multiple URLs, one per line. Titles and captions will be assigned automatically. You can edit them later from media library.</p>\
<textarea name="urls" placeholder="Enter your urls here"></textarea>\
<input type="hidden" name="func" value="url_upload" />\
<br />\
<input type="submit" name="dfdf" value="Upload Now" />\
</form>\
<div class="mlib-ajax-result"></div>\
<div class="mlib-uploaded-preview"></div>\
<div style="clear:both;"></div>\
</div></div></div></div></div>\
<div id="mlib_chooser">\
<div id="mlib_chooser_bg"></div>\
<div id="mlib_chooser_body">\
<div id="mlib_chooser_head">Choose what to import</div>\
<div id="mlib_chooser_data"></div>\
<a class="mlib-button close-mlib-chooser">Cancel</a>\
</div></div></div>';


if($('.mlib-close').length==1){
$('.mlib-close').trigger('click');
}

$('body').append(xhtml);
var mlib_upl_allowed = '.' + mlib_replace_all(allowed, ',', '|.');
mlib_upl_allowed = mlib_replace_all(mlib_upl_allowed, '|', ',');
if (mlib_is_ie() < 10) {
 // is IE version less than 10
//mlib_load_gallery_data();
} else {
 // is IE 10 and later or not IE
var fid = $('.mlib-main').attr('id');
if(allowed==""){
var myDropzone = new Dropzone(".dropzone",{
	dictDefaultMessage:"", 
	maxFilesize:xmaxFilesize, 
	parallelUploads:xparallelUploads, 
	thumbnailWidth:xthumbnailWidth, 
	thumbnailHeight:xthumbnailHeight, 
	acceptedFiles:'jpg,png,gif,jpeg,txt,zip,rar,doc,docx,ppt,pptx,xls,xlsx,csv,tar,gz', 
	url: mlib_domain+"mlib-upload.php?fid="+fid});
 } else {
if(mlib_upload_resolution==''){
var myDropzone = new Dropzone(".dropzone",{dictDefaultMessage:"", maxFilesize:xmaxFilesize, parallelUploads:xparallelUploads, thumbnailWidth:xthumbnailWidth, thumbnailHeight:xthumbnailHeight, acceptedFiles:mlib_upl_allowed, url: mlib_domain+"mlib-upload.php?fid="+fid});
} else {
if(mlib_upload_resolution=='4k'){var resval = 3840;}
if(mlib_upload_resolution=='8k'){var resval = 7680;}
if(mlib_upload_resolution=='16k'){var resval = 15360;}
var myDropzone = new Dropzone(".dropzone",{timeout: 0, dictDefaultMessage:"", resizeWidth: resval, resizeHeight: resval, resizeMethod: 'contain', resizeQuality: 0.8, maxFilesize:xmaxFilesize, parallelUploads:xparallelUploads, thumbnailWidth:xthumbnailWidth, thumbnailHeight:xthumbnailHeight, acceptedFiles:mlib_upl_allowed, url: mlib_domain+"mlib-upload.php?fid="+fid});
}

}
myDropzone.on("queuecomplete", function(x) {
mlib_thumbs_after_upload();
});
}

//$(".dropzone").dropzone({ url: mlib_domain+"mlib-upload.php" });
mlib_load_gallery_data();
load_import_options();
}

function mlib_replace_all(inSource, inToReplace, inReplaceWith){
var outString = inSource;
while (true) {
var idx = outString.indexOf(inToReplace);
if (idx == -1) {
break;}
outString = outString.substring(0, idx) + inReplaceWith +
outString.substring(idx + inToReplace.length);
}return outString;
}



function mlib_toggle_fileinfo(){
$('.mlib-item-properties').toggle();
}









(function ( $ ) {

$.fn.mlibready = function( options ) {
// This is the easiest way to have default options.
var settings = $.extend({
allowed: "jpg,png,gif,jpeg",
minselect: 1,
maxselect: 999999999999,
returnas: 0,
admin: 0,
returnto: '',
runfunction: 'null',
maxFilesize: 1000,
parallelUploads: 4,
thumbnailWidth: 150,
thumbnailHeight: 150,
folderID: '',
mcename: '',
ckename: ''
}, options );


this.click(function(){ 
launch_mlib_box(settings.allowed, settings.returnto, settings.returnas, settings.maxselect, settings.minselect, settings.admin, settings.runfunction, settings.maxFilesize, settings.parallelUploads, settings.thumbnailWidth, settings.thumbnailHeight, settings.folderID, settings.mcename, settings.ckename);
});

};
}( jQuery ));


function mlib_size(bytes, precision)
{  
    var kilobyte = 1024;
    var megabyte = kilobyte * 1024;
    var gigabyte = megabyte * 1024;
    var terabyte = gigabyte * 1024;
   
    if ((bytes >= 0) && (bytes < kilobyte)) {
        return bytes + ' B';
 
    } else if ((bytes >= kilobyte) && (bytes < megabyte)) {
        return (bytes / kilobyte).toFixed(precision) + ' KB';
 
    } else if ((bytes >= megabyte) && (bytes < gigabyte)) {
        return (bytes / megabyte).toFixed(precision) + ' MB';
 
    } else if ((bytes >= gigabyte) && (bytes < terabyte)) {
        return (bytes / gigabyte).toFixed(precision) + ' GB';
 
    } else if (bytes >= terabyte) {
        return (bytes / terabyte).toFixed(precision) + ' TB';
 
    } else {
        return bytes + ' B';
    }
}



/* Used to detect IE version. Wont work for IE 11+ */
function mlib_is_ie() {
  var myNav = navigator.userAgent.toLowerCase();
  return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : 1000;
}

function mlib_adjust_iframe(){
if($('#mlib_fallback_iframe').length==1){
var obj = document.getElementById("mlib_fallback_iframe");
obj.style.height = (obj.contentWindow.document.body.scrollHeight+20) + 'px';
}
}

function mlib_menu_toggle(){
$('.mlib-left').toggleClass('mlib-menu-left-hide');
}

function mlib_thumbs_after_upload(){
$('#mlib-media-li').trigger('click');
$('.mlib-left').removeClass('mlib-menu-left-hide');
$('.mlib-display-canvas').html('');
mlib_load_gallery_data();
}

function init_ckeditor_medialib(editorx){
if($('.cke_button__ck_mlib_button_'+editorx).length==0){
	setTimeout(function() {
    init_ckeditor_medialib(editorx);
	}, 5000);
} else {
$('.cke_button__ck_mlib_button_'+editorx+'_label').css('display','inline');
$('.cke_button__ck_mlib_button_'+editorx).mlibready({allowed:"jpg,png,gif,jpeg,txt,zip,rar,doc,docx,ppt,pptx,xls,xlsx,csv,tar,gz", returnas:'all', ckename:editorx, admin:1});
}
}
gal_rrcop_quality = 0.4;

jQuery(document).ready(function(){

/* delaying is necessary for onload click */
setTimeout("radioToggle()", 200);


/* click radio buttons to show or hide their collapse divs */
jQuery('body').on('click', '.radiobtn', function(){
radioToggle();
});

jQuery('body').on('click', '.confirm', function(e){
e.preventDefault();
var xurl = $(this).attr('href');
phpl_confirm('Are you sure?', function(){
document.location.href = xurl;
}, 'xxconfirm');


});

/* set album cover */
jQuery('body').on('click', '.mlib-album-cover', function(){
var photoID = $('.mlib-single-edit [name="mlibid"]').attr('value');
var photoURL = $('[mlib-id="'+photoID+'"]').attr('mlib-url') || '';
var xhtml = 'Original image may be very big and time consuming for slow internet connection.<br><br> Which version of photo to open in crop mode for setting thumbnail?';

if(photoURL==''){
var fallbackFooter = '<div class="phpl-alert-btn-danger phpl-alert-close">Cancel</div><div onclick="mlib_cover_UI(\'full\')" class="phpl-alert-btn-info">Original</div><div onclick="mlib_cover_UI(\'fhd\')" class="phpl-alert-btn-success">Full HD</div>';
phpl_alert(xhtml, 'Choose Quality', fallbackFooter);
return;
}

phpl_alert(xhtml, 'Choose Quality', '<div class="phpl-alert-btn-danger phpl-alert-close">Cancel</div>');
$('.phpl-alert-box').addClass('phpl-alert-loading');
admin_check_ai_photo_exists(photoURL).done(function(exists){
var xfooter = mlib_cover_footer_html(exists);
$('.phpl-alert-box').removeClass('phpl-alert-loading');
$('.phpl-alert-box-bottom').html(xfooter);
}).fail(function(){
var xfooter = mlib_cover_footer_html(false);
$('.phpl-alert-box').removeClass('phpl-alert-loading');
$('.phpl-alert-box-bottom').html(xfooter);
});

});

});

function mlib_cover_footer_html(hasAi){
var xfooter = '<div class="phpl-alert-btn-danger phpl-alert-close">Cancel</div><div onclick="mlib_cover_UI(\'full\')" class="phpl-alert-btn-info">Original</div><div onclick="mlib_cover_UI(\'fhd\')" class="phpl-alert-btn-success">Full HD</div>';
if(hasAi){
xfooter += '<div onclick="mlib_cover_UI(\'ai\')" class="phpl-alert-btn-warning">AI</div>';
}
xfooter += '<div style="clear:both;"></div>';
return xfooter;
}

function admin_is_webp_quality(quality){
return quality=='2k' || quality=='fhd' || quality=='hd' || quality=='ai';
}

function admin_quality_filename(file, quality){
var filename = file.split('/').pop();
if(!admin_is_webp_quality(quality)){return filename;}
var dot = filename.lastIndexOf('.');
if(dot===-1){return filename+'.webp';}
return filename.substring(0, dot)+'.webp';
}

function admin_quality_url_from_original(file, quality){
return main_domain+quality+'/'+admin_quality_filename(file, quality);
}

function admin_ai_file_from_original(file){
return admin_quality_filename(file, 'ai');
}

function admin_ai_url_from_original(file){
return main_domain+'ai/'+admin_ai_file_from_original(file);
}

function admin_check_ai_photo_exists(file){
var aiUrl = admin_ai_url_from_original(file);
var deferred = $.Deferred();
$.ajax({
url: aiUrl,
type: 'HEAD'
}).done(function(){
deferred.resolve(true);
}).fail(function(){
deferred.resolve(false);
});
return deferred.promise();
}

function mlib_cover_UI(xqual){
$('.phpl-alert-box').addClass('phpl-alert-loading');
var photoID = $('.mlib-single-edit [name="mlibid"]').attr('value');
var photoURL = $('[mlib-id="'+photoID+'"]').attr('mlib-url');
var renderCropper = function(sourceUrl){
phpl_close_alert();
$('body').append('<div class="mlib-crop-ctr"><div class="mlib-crop-bg"></div><div class="mlib-crop-area"><img id="mlib-crop" onload="mlib_init_rcrop()" src="'+sourceUrl+'"></div><div class="mlib-crop-buttons"><a href="#" onclick="mlib_rcrop_apply()" class="mlib-button mlib-button-blue">Apply</a> <a href="#" onclick="mlib_rcrop_close()" class="mlib-button-red">Cancel</a></div></div>');
$('#wrapper, .mlib-main').addClass('blur');
};

if(xqual=='ai'){
renderCropper(admin_ai_url_from_original(photoURL)+'?t='+(new Date()).getTime());
return;
}

var xpURL = main_domain+'phpix-download.php?q='+encodeURIComponent(xqual)+'&f='+encodeURIComponent(photoURL)+'&prepare=1';
$.get( xpURL , function() {
renderCropper(admin_quality_url_from_original(photoURL, xqual));
});
}

function admin_toast(xtext){
jQuery('.gal-toast').remove();
jQuery('#flscrn').append('<div class="gal-toast"><div class="gal-toast-text">'+xtext+'</div></div>');
jQuery(".gal-toast").delay(5000).fadeOut("slow", function(){
jQuery('.gal-toast').remove();
});
}

function mlib_rcrop_apply(){
var srcOriginal = $('#mlib-crop').rcrop('getDataURL', 750, 750);
var xxaid = $('#mlib-lightbox').attr('mlib-return-to');
var xaid = xxaid.replace('#div-', '');
$('.mlib-crop-buttons').hide();
$.post( mlib_domain+"mlib.php", {func:'mlib_set_cover', aid:xaid, photo:srcOriginal} , function(datax) {
d = new Date();
$('#row-'+xaid+' .album-thumb').attr('src', main_domain+'cover/'+datax+'?t='+d.getTime());
admin_toast('Cover changed successfully');
$('.mlib-crop-buttons').show();
mlib_rcrop_close();
});
}

function admin_delete_spot(xthis){
	
var xxid = $(xthis).closest('tr').attr('id');
var xid = xxid.replace('sid-', '');
var spot = $('#'+xxid+' .admin-spot-title').text();

// Display confirmation modal
phpl_confirm('Are you sure you want to delete spot <b>'+spot+' </b>?', function() {
document.location.href = main_domain+'phpix-manage.php?page=spots&delete='+xid;
}, xid);
}


function mlib_rcrop_close(){
$('#mlib-crop').rcrop('destroy');
$('#wrapper, .mlib-main').removeClass('blur');
$('.mlib-crop-ctr').remove();
}

function album_delete(aid){
var album = $('#row-'+aid+' .album-title').html();
// Display confirmation modal
phpl_confirm('Are you sure you want to delete album : <br><b>'+album+' ?</b>', function() {

const tot = parseInt($('#row-'+aid+' .album-count').text());
if(tot==0){
document.location.href = main_domain+'phpix-manage.php?page=albums&delete='+aid;
} else {
phpl_close_alert(aid);
phpl_alert('There are <b>'+tot+' photos</b> in <b>'+album+'</b>. An album can only be deleted after deleting all photos in that album!', 'Unable to delete..');
}
}, aid);

}

function mlib_init_rcrop(){

var windowWidth = window.innerWidth-60;
var windowHeight = window.innerHeight-60;

$('#mlib-crop').css('max-width', windowWidth+'px');
$('#mlib-crop').css('max-height', windowHeight+'px');
$('#mlib-crop').removeAttr('onload');

$('#mlib-crop').rcrop({
	grid:true,
	minSize:[200,200],
	preserveAspectRatio:true,
	preview: {
        display: true,
        size : [200,200],
    }
	});
}


// function runs after close button is clicked or X is clicked
function album_after_function(xselection, xselector){
var xaid = xselector.replace('#div-','');
$.post( mlib_domain+"mlib.php", {func:'mlib_update_album_count', aid:xaid} , function(datax) {
$('#row-'+xaid+' .album-count').html(datax);
});
}

function album_manage(xthis, albumID){
album_text_modify(xthis, albumID);
$(xthis).attr('id','link-'+albumID);
$(xthis).attr('onclick', "album_text_modify(this, '"+albumID+"')");
$('#div-'+albumID).remove();
$('body').append('<div style="display:none;" id="div-'+albumID+'"></div>');
$('#link-'+albumID).mlibready({returnto:'#div-'+albumID, maxselect:1, folderID:albumID, runfunction: 'album_after_function'});
$(xthis).trigger('click');
}



function album_text_modify(xthis, albumID){
var xname = $(xthis).closest('.album-info').find('.album-title').text();
mlib_vars_tab1_text = 'Upload Photos';
mlib_vars_h1_text = 'Upload Photos : '+xname;
mlib_vars_tab2_text = 'Manage Gallery';
mlib_vars_h2_text = 'Manage gallery : '+xname;
mlib_vars_tab3_text = 'Insert from URL';
mlib_vars_h3_text = 'Upload via URL : '+xname;
mlib_vars_insert_button_text = 'Close';
}


/* toggles hidden radio div */
function radioToggle(){

jQuery('.radiobtn').each(function(){
if(jQuery(this).prop('checked')==true){
jQuery(this).closest('.radiotoggle').find('.collapse').show();
} else {
jQuery(this).closest('.radiotoggle').find('.collapse').hide();
}
});
}

function toggle_all_checkboxes(xthis){

var xname = $(xthis).attr('data-chk');

if($(xthis).is(':checked')){
$('[type="checkbox"][name="'+xname+'"]').prop('checked', true);
} else {
$('[type="checkbox"][name="'+xname+'"]').prop('checked', false);
}

}

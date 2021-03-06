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
if(confirm('Are you sure?')){
document.location.href = xurl;
}
});

/* set album cover */
jQuery('body').on('click', '.mlib-album-cover', function(){
var xfooter = '<div class="phpl-alert-btn-danger phpl-alert-close">Cancel</div><div onclick="mlib_cover_UI(\'full\')" class="phpl-alert-btn-info">Original</div><div onclick="mlib_cover_UI(\'hd\')" class="phpl-alert-btn-warning">HD</div><div onclick="mlib_cover_UI(\'fhd\')" class="phpl-alert-btn-success">Full HD</div>';
var xhtml = 'Original image may be very big and time consuming for slow internet connection.<br><br> Which version of photo to open in crop mode for setting thumbnail?';
phpl_alert(xhtml, 'Choose Quality', xfooter);

});

});

function mlib_cover_UI(xqual){
$('.phpl-alert-box').addClass('phpl-alert-loading');
var photoID = $('.mlib-single-edit [name="mlibid"]').attr('value');
var photoURL = $('[mlib-id="'+photoID+'"]').attr('mlib-url');
var xpURL = main_domain+'phpix-download.php?q='+xqual+'&f='+photoURL;
$.get( xpURL , function() {
phpl_close_alert();
$('body').append('<div class="mlib-crop-ctr"><div class="mlib-crop-bg"></div><div class="mlib-crop-area"><img id="mlib-crop" onload="mlib_init_rcrop()" src="'+main_domain+''+xqual+'/'+photoURL+'"></div><div class="mlib-crop-buttons"><a href="#" onclick="mlib_rcrop_apply()" class="mlib-button mlib-button-blue">Apply</a> <a href="#" onclick="mlib_rcrop_close()" class="mlib-button-red">Cancel</a></div></div>');
$('#wrapper, .mlib-main').addClass('blur');
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
alert('Cover changed successfully');
$('.mlib-crop-buttons').show();
mlib_rcrop_close();
});
}

function mlib_rcrop_close(){
$('#mlib-crop').rcrop('destroy');
$('#wrapper, .mlib-main').removeClass('blur');
$('.mlib-crop-ctr').remove();
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




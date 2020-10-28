var gal_hide_cursor;
var gal_hide_zoom_lvl;
var max_thumb_width=0;
var gal_progress_bar_max=0;
var gal_counter_hide_var;
var gal_vars_slideshow=false;
var gal_vars_slide_time=10;
var gal_vars_elapsed_time=0;
var gal_vars_slide_timer;
var gal_vars_remainingURL = '';
var gal_vars_single_mode = false;
var gal_vars_single_photo;
var gal_init_fullscreen = true;
gal_rrcop_quality = 0.9;
var gal_share_network_keys = new Array("fb", "tw", "gp", "pi", "wh");
var gal_share_network_names = new Array("Facebook", "Twitter", "Google+", "Pinterest", "WhatsApp");
var gal_vars_rotate_preview_timer;
var gal_vars_orientation;
var gal_vars_mobile_control;
var gal_vars_zoom_text_timer;
var gal_reposition_reel_timer;
var gal_resize_slow_timer;
var gal_vars_pan_duration = 300;
var gal_vars_tapTimer = null;
var gal_vars_expand = 'disabled';
var gal_vars_rotation = 0;
var gal_vars_thumbsize = 40;
var gal_xtag_reinit_timer;
var gal_vars_touchtip_timer;
var gal_vars_parentFolder = '';
var gal_main_slide_timer;
var gal_vars_notes_ipp = 6;
var gal_pixq = 'automatic';
var gal_brightness_elements = '#gal-reel-now-inner img, .flex-images .item img, .album-photo-wrap img';

var getQueryString = function ( field, url ) {
	var href = url ? url : window.location.href;
	var reg = new RegExp( '[?&]' + field + '=([^&#]*)', 'i' );
	var string = reg.exec(href);
	return string ? string[1] : null;
};


function gal_fitscreen(){
gal_mobile_controls('hide');
if(gal_vars_expand=='disabled'){
gal_vars_expand = 'enabled';
jQuery('body').addClass('gal-zoomfit');
gal_toast('CROP to FIT enabled');
} else {
gal_vars_expand = 'disabled';
jQuery('body').removeClass('gal-zoomfit');
gal_toast('CROP to FIT diabled');
}
$(window).trigger('resize');
}


function album_login(){
album_toggle_sidebar();
var xhtml = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>\
<div id="phpl-alert-notify"><p>To access private stuff, please login.</p><br /></div><form method="post" name="passform" id="passform">\
<table class="phpl-alert-table">\
<tr><td width="90">Email</td><td><input type="text" id="email" name="email"></td></tr>\
<tr><td width="90">Password</td><td><input autocomplete="off" type="password" id="passkey" name="passkey"></td></tr>\
<tr><td colspan="2">\
<div style="margin:0 auto;" class="g-recaptcha" data-sitekey="'+gal_sitekey+'">reCaptcha loading...</div>\
</td></tr>\
</table></form>';

var fhtml = '<div class="phpl-alert-btn-danger phpl-alert-close">Close</div> <div onclick="album_login_start()" class="phpl-alert-btn-success">Login</div>';
phpl_alert(xhtml, 'Login to continue...', fhtml);
}


function album_toggle_sidebar(){
$('#album-sidebar').toggleClass('album-sidebar-show');
}


function album_get_pwd(){
album_toggle_sidebar();
var xhtml = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>\
<div id="phpl-alert-notify"><p>Your password will be sent to your email account.</p><br /></div><form method="post" id="passform" name="passform">\
<table class="phpl-alert-table">\
<tr><td width="90">Your email</td><td><input type="text" id="email" name="email"></td></tr>\
<tr><td colspan="2">\
<div style="margin:0 auto;" class="g-recaptcha" data-sitekey="'+gal_sitekey+'"></div>\
</td></tr>\
</table><input type="hidden" name="xurl" value="'+window.location.href+'"></form>';

var fhtml = '<div class="phpl-alert-btn-danger phpl-alert-close">Close</div> <div onclick="album_get_pwd_start()" class="phpl-alert-btn-success">Send Email</div>';
phpl_alert(xhtml, 'Retrieve password', fhtml);
}


function album_start_loading(){
$('.phpl-alert-box').addClass('phpl-alert-loading');
}

function album_end_loading(){
$('.phpl-alert-box').removeClass('phpl-alert-loading');
}


function album_get_pwd_start(){
album_start_loading();
$.post(gal_domain+'phpix-ajax.php?method=get_password', $('#passform').serialize(), function(data){
$('#phpl-alert-notify').html(data);
album_end_loading();
grecaptcha.reset();
});
}


function album_loadslow(){
var tot = $('.loadslow').length;
if(tot != 0){
var xsrc = $('.loadslow:first').attr('xsrc');
$('.loadslow:first').attr('onload', 'album_loadslowxx(this)');
$('.loadslow:first').attr('src', xsrc);
}
}

function album_loadslowxx(xthis){
$(xthis).closest('.album-photo-wrap').css('background-color', '#fff');
$(xthis).removeClass('loadslow');
setTimeout("album_loadslow()", 10);
}

function album_change_pwd(){
album_toggle_sidebar();
var xhtml = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>\
<div id="phpl-alert-notify"></div><form autocomplete="off" method="post" id="passform" name="passform"><table class="phpl-alert-table">\
<tr><td width="90">Current Password</td><td><input type="password" id="passkey" name="passkey"></td></tr>\
<tr><td width="90">New Password</td><td><input type="password" id="newpasskey" name="newpasskey"></td></tr>\
<tr><td width="90">Confirm new Password</td><td><input type="password" id="cpasskey" name="cpasskey"></td></tr>\
<tr><td colspan="2">\
<div style="margin:0 auto;" class="g-recaptcha" data-sitekey="'+gal_sitekey+'"></div>\
</td></tr>\
</table></form>';

var fhtml = '<div class="phpl-alert-btn-danger phpl-alert-close">Close</div> <div onclick="album_change_pwd_start()" class="phpl-alert-btn-success">Change</div>';
phpl_alert(xhtml, 'Change password...', fhtml);
}

function album_change_pwd_start(){
var xpwd = $('#passkey').val();
var npwd = $('#newpasskey').val();
var cpwd = $('#cpasskey').val();

if(xpwd.length<8){
$('#phpl-alert-notify').html('<div class="phpl-notify-warning"><b>Current password</b> is too short! Minimum 8 charectors please!</div>');
}

else if(npwd.length<8){
$('#phpl-alert-notify').html('<div class="phpl-notify-warning"><b>New password is</b> too short! Minimum 8 charectors please!</div>');
}

else if(cpwd!=npwd){
$('#phpl-alert-notify').html('<div class="phpl-notify-error"><b>New password</b> and <b>Confirm password</b> do not match. Please retry!</div>');
}

else {
album_start_loading();
$.post(gal_domain+'phpix-ajax.php?method=change_password', $('#passform').serialize(), function(data){
$('#phpl-alert-notify').html(data);
album_end_loading();
grecaptcha.reset();
});
}
}


function album_nav_keys(){
album_toggle_sidebar();
var xhtml = '<table class="phpl-alert-table">\
<tr><th>Name</th><th>Alternative</th><th>Action</th></tr>\
<tr><td>Right</td><td>Swipe left</td><td>Next Photo</td></tr>\
<tr><td>Left</td><td>Swipe right</td><td>Previous Photo</td></tr>\
<tr><td>Shift + Space</td><td><i class="album-nav album-nav-play"></i></td><td>On / Off slideshow</td></tr>\
<tr><td>Shift + r</td><td><i class="album-nav album-nav-rotate"></i></td><td>Rotate current photo</td></tr>\
<tr><td>Shift + i</td><td><i class="album-nav album-nav-picinfo"></i></td><td>Current Photo Information</td></tr>\
<tr><td>Shift + t</td><td>From settings</td><td>On / Off mini thumbnails</td></tr>\
<tr><td>Shift + x</td><td>From download</td><td>Edit &amp; save current photo</td></tr>\
<tr><td>Shift + z</td><td>Mouse Wheel scroll, pinch-in</td><td>On / Off Pan &amp; Zoom for current photo</td></tr>\
<tr><td>Shift + b</td><td>From settings</td><td>Adjust brightness</td></tr>\
</table>';
phpl_alert(xhtml, 'Keyboard Navigation Buttons');
}


function album_login_start(){
var xpwd = $('#passkey').val();
var xid = $('#email').val();
album_start_loading();
$.post(gal_domain+'phpix-ajax.php?method=login', $('#passform').serialize(), function(data){
album_end_loading();
grecaptcha.reset();
$('#phpl-alert-notify').html(data);
});
}


function album_logout_start(){
album_start_loading();
$.post(gal_domain+'phpix-ajax.php?method=logout', function(data){
album_end_loading();
document.location.reload();
});
}


function album_logout(){
album_toggle_sidebar();
var fhtml = '<div class="phpl-alert-btn-danger phpl-alert-close">Close</div> <div onclick="album_logout_start()" class="phpl-alert-btn-success">Logout</div>';
phpl_alert('You are about to logout. You wont be able to access private albums. Are you sure? if yes, press the logout button.', 'Are you sure?', fhtml);
}



function gal_touchtip(msg){
gal_touchtip_remove();
$('body').append('<div class="gal-touchtip-spacer">&nbsp;</div><div class="gal-touchtip"><b>Note : </b>'+msg+'</div>');
clearTimeout(gal_vars_touchtip_timer);
gal_vars_touchtip_timer = setTimeout(function(){gal_touchtip_remove();}, 3000); 
}

function gal_touchtip_remove(){
$('.gal-touchtip, .gal-touchtip-spacer').remove();
}


function gal_set_quality(){

var xval = localStorage.getItem('gal_setting_pixq');
if(xval !== null){ // if null
gal_pixq = xval;
}


if(gal_pixq=='automatic'){

if(screen.width<screen.height){var xw = screen.width;} else {var xw = screen.height;}

var init_quality = gal_quality;
var cw = parseInt(xw*window.devicePixelRatio);

if(window.devicePixelRatio<=1){ // for desktop browsers
	if(cw >= 1080){
	gal_quality = 'full';
	gal_vars_thumbsize = 100;
	} else if(cw >= 720){
	gal_vars_thumbsize = 70;
	gal_quality = 'fhd';
	} else if(cw >= 480){
	gal_vars_thumbsize = 50;
	gal_quality = 'hd';
	} else {
	gal_vars_thumbsize = 30;
	gal_quality = 'qhd';
	}
} else { // mobiles and tablets
	if(cw >= 1920){
	gal_vars_thumbsize = 50;
	gal_quality = 'full';
	} else if(cw >= 1080){
	gal_vars_thumbsize = 40;
	gal_quality = 'fhd';
	} else if(cw >= 720){
	gal_vars_thumbsize = 40;
	gal_quality = 'hd';
	} else {
	gal_vars_thumbsize = 30;
	gal_quality = 'qhd';
	}

}
} else {
gal_quality = xval;
}

}


function album_info_toggle(){
$('#album-info').toggle();
}



function gal_touch_controls(){
$('.gal-mobile-controls').show();
clearTimeout(gal_vars_mobile_control);
gal_vars_mobile_control = setTimeout("$('.gal-mobile-controls').hide()", 4000);

if($('.gal-zoomed').length==1){$('.gal-prev, .gal-next').fadeTo(10, 1);$('.gal-prev, .gal-next').show();} else {$('.gal-prev, .gal-next').hide();}

}



function gal_demo(){

var elem = document.getElementById('gal-reel');


panzoom = Panzoom(elem, {
maxScale: 1,
minScale: 1,
startScale: 1,
contain: 'outside',
duration: gal_vars_pan_duration,
startX: -($(window).width()),
animate: true,
step: 0.1
});

elem.addEventListener('panzoomzoom', (event) => {
if ($('.gal-zoomed').length==0){
start_zoom();
}
});

elem.parentElement.addEventListener('wheel', panzoom.zoomWithWheel);

elem.addEventListener('panzoomend', (event) => {
//clearTimeout(gal_reposition_reel_timer);
gal_reposition_reel_timer = setTimeout("reposition_reel()", 10);
});

elem.addEventListener('panzoomstart', (event) => {
gal_main_slide_timer = setTimeout("gal_main_slide()", 100);
});


}


function gal_main_slide(){
var val = panzoom.getPan();
var xw = $(window).width(); //400 is screen width or element width
var maxpan = (((-1)*(100+0))/100)*xw; // -20% threshold
var minpan = (((-1)*(100-0))/100)*xw; // +20% threshold

if(val.x>=maxpan && val.x<=minpan){ // goto slide 2
} else if(val.x<maxpan){ // goto slide 3
$('#gal-reel-now-inner').attr('class', 'gal-main-slide-right');
} else { // goto slide 1
$('#gal-reel-now-inner').attr('class', 'gal-main-slide-left');
}

}


function gal_gotoURL(xinput){
var xurl = xinput;
$('#album-list-block, #album-notes-block').hide();
$('#album-pics-block').show();
var aid = getQueryString('aid', xurl);
$.get( gal_domain+"phpix-ajax.php?method=album_photos&aid="+aid+"&q="+gal_quality, function(data) {
$('#album-pics-block').html(data);
gal_vars_aid = aid;
$('title').html('PHPix album : '+aid);
window.history.pushState( {} , 'phpix', gal_domain+'phpix-alt.php?a='+aid );
// adds ordering and temporary ids
gal_progress_bar_max = $('#new_thumbs > li').length;
gal_json_to_tags();
gal_orientation();
//alert(gal_vars_remainingURL+'rerer');
if(gal_vars_remainingURL!='' && gal_vars_remainingURL!='a'){
var aid_arr = gal_vars_remainingURL.split('.');
var aid_tot = aid_arr.length;
var aid_tot_int = aid_tot - 1;
xnaid = aid_arr[aid_tot_int];
gal_vars_remainingURL = gal_vars_remainingURL.replace('.'+xnaid, '');
var xurl = gal_domain+''+albumFILE+'?aid='+xnaid;
setTimeout(function(){
$('.album-list b[xurl="'+xnaid+'"]').trigger('click');

}, 100);

}

});
}


function album_notes(){
$('#album-list-block, #album-pics-block').hide();
$('#album-notes-block').show();

if($('.album-notes').html()==''){
var xpage = getQueryString("pagenumber");
if(isNaN(xpage) || xpage==null){
get_album_notes(1);
} else {
get_album_notes(xpage);
}
}

}

function get_album_notes(xpage=1){
$('.album-notes').html('<div class="gal_loading"></div>');
$.get(gal_domain+'phpix-ajax.php?method=album_notes&ipp='+gal_vars_notes_ipp+'&pagenumber='+xpage, function(data){
$('.album-notes').html(data);
window.history.replaceState( {}, 'phpix', gal_domain+''+albumFILE+'?tab=notes&ipp='+gal_vars_notes_ipp+'&pagenumber='+xpage );
album_notes_readmore();
});
}

function album_notes_readmore(){
$(this).css('overflow', 'auto');
$('.album-note-box').each( function() {
    if ($(this).prop('scrollHeight') > $(this).prop('clientHeight')){
    $('.album-note-more', this).css('display', 'block');
	} else {
	$('.album-note-more', this).css('display', 'none');
	}
$(this).css('overflow', 'hidden');
});
}


function album_note_expand(xthis){
$(xthis).closest('.album-note-ctr').find('.album-note-box').addClass('album-note-expanded').removeClass('album-note-boxed');
$('#album-notes-block .album-buttons').append('<li onclick="album_note_close()" class="albtn-close"></li>');
}

function album_note_close(){
$('.album-note-box').addClass('album-note-boxed');
$('.album-note-expanded').removeClass('album-note-expanded');
$('#album-notes-block .albtn-close').remove();
}

function album_gallery_main(){
album_toggle_sidebar();
$('#album-list-block, #album-pics-block, #album-notes-block').css('display', 'none');
$('#album-list-block').css('display', '');
$('#album-pics-block').html('');
$('#album-notes-block .albtn-close').trigger('click');
$('title').html('PHPix gallery');
window.history.pushState( {} , 'phpix', gal_domain+''+albumFILE );
}


function album_closePhotos(){
if(gal_vars_parentFolder!=''){
var nurl = gal_domain+''+albumFILE+'?aid='+gal_vars_parentFolder;
gal_gotoURL(nurl);
} else {
$('#album-list-block').show();
$('#album-notes-block').hide();
$('#album-pics-block').hide().html('');
$('title').html('PHPix gallery');
window.history.pushState( {} , 'phpix', gal_domain+''+albumFILE );
}
}


function gal_close_zoom(){
jQuery('.gal-zoomed').remove();
gal_toast('ZOOM deactivated!');
}

function start_zoom(){
gal_toast('PAN and ZOOM activated');

var cid = $('.fullscreen-item').attr('data-count');
var xthumb = $('.fullscreen-item > a > img').attr('src');
var ximg = xthumb.replace('/thumb/','/'+gal_quality+'/');
$('.gal-bg').append('<div class="gal-zoomed"><div class="gal-zoomed-inner"><img id="gal-zoomed-img" style="'+gal_get_css(cid)+'" src="'+ximg+'"></div></div>');


const elem2 = document.getElementById('gal-zoomed-img');

panzoom2 = Panzoom(elem2, {
maxScale: 10,
minScale: 0.5,
startScale: 1.5,
step: 0.5
});


const xparent = elem2.parentElement;
// No function bind needed
xparent.addEventListener('wheel', panzoom2.zoomWithWheel);

elem2.addEventListener('panzoomzoom', (event) => {
var zoomx = panzoom2.getScale();
$('.zoomed-in-text').html(parseFloat(zoomx).toFixed(1)+'x ZOOM');

if(zoomx==1){
$('.zoomed-in-text').hide();
} else {
$('.zoomed-in-text').show();
$('.gal-counter').hide();
clearTimeout(gal_vars_zoom_text_timer);
gal_vars_zoom_text_timer = setTimeout(function(){
$('.zoomed-in-text').hide();
}, 2000);
}

});

if (window.panzoom !== undefined){
//panzoom.destroy();
//delete window.panzoom;
}
}



function gal_shift_next(){
//$('.dbug').append('<p>'+gal_quality+'</p>');
$('.gal-zoomed').remove();

var cid = parseInt(jQuery('#gal-reel-next img').attr('data-id'));
$('#gal-reel-prev').remove();
$('#gal-reel-now').attr('id', 'gal-reel-prev');
$('#gal-reel-now-inner').attr('id', 'gal-reel-prev-inner');
$('#gal-reel-next').attr('id', 'gal-reel-now');
$('#gal-reel-next-inner').attr('id', 'gal-reel-now-inner');

var xtype = $('#gal-item-'+cid).attr('data-xtype');
$('#gal-ext-app').remove();
$('#gal-reel-now-inner').append('<div id="gal-ext-app" class="gal-xtype-'+xtype+'"></div>');

var maxid = parseInt($('.fullscreen-item').closest('[data-items]').attr('data-items'));

var next_id = cid + 1;
if(cid==(maxid-1)){next_id = 0;}

var next_thumb = $('#gal-item-'+next_id+' img').attr('xsrc');
var next_url = next_thumb.replace('/thumb/', '/'+gal_quality+'/');

$('.fullscreen-item').removeClass('fullscreen-item');
$('#gal-item-'+cid).addClass('fullscreen-item');

var xhtml = '<div id="gal-reel-next"><div id="gal-reel-next-inner"><img style="'+gal_get_css(next_id)+'" data-id="'+next_id+'" src="'+next_thumb+'"><div class="gal_loading"></div></div></div>';
$('#gal-reel').append(xhtml);
$('.gal-reel-preloader').append('<img onload="gal_preload_complete(this)" src="'+next_url+'">');
panzoom.pan(-$(window).width(), 0, { animate: false });
gal_sharer();
gal_reset_navigation();
$('.gal-stats').html('<div class="gal_loading"></div>');
gal_ajax_exif();

var xphoto = $('#gal-item-'+cid+' img').attr('src');
var aid = getQueryString('aid');
var uri = xphoto.split('/thumb/');
$('title').html('PHPix photo - '+uri[1]);
window.history.pushState( {} , 'phpix', gal_domain+'phpix-alt.php?u='+uri[1] );
gal_set_thumb();
gal_xtag_reinit();

if(gal_slide_animation=='any-random'){
setTimeout(function(){gal_random_animation();},600);
}
}


function gal_preload_complete(xthis){
var newSRC = $(xthis).attr('src');
var thumbSRC = newSRC.replace('/full/', '/thumb/');
thumbSRC = thumbSRC.replace('/fhd/', '/thumb/');
thumbSRC = thumbSRC.replace('/hd/', '/thumb/');
thumbSRC = thumbSRC.replace('/qhd/', '/thumb/');
$(xthis).removeAttr('onload');

var isXtag = $('#gal-reel img[src="'+thumbSRC+'"]').closest('div').attr('id');
if(isXtag=='xtag-element'){
$('#gal-reel-now-inner').find('.gal_loading').remove();
} else {
$('#gal-reel img[src="'+thumbSRC+'"]').closest('div').find('.gal_loading').remove();
}

$('#gal-reel img[src="'+thumbSRC+'"]').closest('div').find('.gal_loading').remove();
$('#gal-reel img[src="'+thumbSRC+'"]').attr('src', newSRC);
$(xthis).remove();
}


function gal_shift_prev(){
$('.gal-zoomed').remove();
var cid = parseInt(jQuery('#gal-reel-prev img').attr('data-id'));
$('#gal-reel-next').remove();
$('#gal-reel-now').attr('id', 'gal-reel-next');
$('#gal-reel-now-inner').attr('id', 'gal-reel-next-inner');
$('#gal-reel-prev').attr('id', 'gal-reel-now');
$('#gal-reel-prev-inner').attr('id', 'gal-reel-now-inner');

var xtype = $('#gal-item-'+cid).attr('data-xtype');
$('#gal-ext-app').remove();
$('#gal-reel-now-inner').append('<div id="gal-ext-app" class="gal-xtype-'+xtype+'"></div>');


var maxid = parseInt($('.fullscreen-item').closest('[data-items]').attr('data-items'));

var prev_id = cid - 1;
if(cid==0){prev_id = maxid-1;}

var prev_thumb = $('#gal-item-'+prev_id+' img').attr('xsrc');
var prev_url = prev_thumb.replace('/thumb/', '/'+gal_quality+'/');

$('.fullscreen-item').removeClass('fullscreen-item');
$('#gal-item-'+cid).addClass('fullscreen-item');

var xhtml = '<div id="gal-reel-prev"><div id="gal-reel-prev-inner"><img style="'+gal_get_css(prev_id)+'" data-id="'+prev_id+'" src="'+prev_thumb+'"><div class="gal_loading"></div></div></div>';
$('#gal-reel').prepend(xhtml);
$('.gal-reel-preloader').append('<img onload="gal_preload_complete(this)" src="'+prev_url+'">');
panzoom.pan(-$(window).width(), 0, { animate: false });
gal_sharer();
gal_reset_navigation();
$('.gal-stats').html('<div class="gal_loading"></div>');
gal_ajax_exif();

var xphoto = $('#gal-item-'+cid+' img').attr('src');
var aid = getQueryString('aid');
var uri = xphoto.split('/thumb/');
$('title').html('PHPix photo - '+uri[1]);
window.history.pushState( {} , 'phpix', gal_domain+'phpix-alt.php?u='+uri[1] );
gal_set_thumb();
gal_xtag_reinit();
if(gal_slide_animation=='any-random'){
setTimeout(function(){gal_random_animation();},600);
}
}

function gal_xtag_reinit(){
$('.xtag-ctr, .xtag-tag').remove();
$('.xtag-img').unwrap('#xtag-element');
$('.xtag-img').removeClass('xtag-img');
clearTimeout(gal_xtag_reinit_timer);
gal_xtag_reinit_timer = setTimeout(function(){
xtag_init();
}, 200);
}

function reposition_reel(){
	
setTimeout(function(){
$('#gal-reel-now-inner').removeAttr('class');
}, gal_vars_pan_duration);


var val = panzoom.getPan();

var xw = $(window).width(); //400 is screen width or element width
var maxpan = (((-1)*(100+20))/100)*xw; // -20% threshold
var minpan = (((-1)*(100-20))/100)*xw; // +20% threshold

$('.gal-reel-panned').removeClass('gal-reel-panned');
$('#gal-reel-now').addClass('gal-reel-panned');
if(val.x>=maxpan && val.x<=minpan){ // goto slide 2
panzoom.pan((-1)*xw, 0, {animate:true});
} else if(val.x<maxpan){ // goto slide 3
panzoom.pan((-1)*xw*2,0,{animate:true});
setTimeout(function(){
gal_shift_next();
}, gal_vars_pan_duration);
$('#gal-reel-next').addClass('gal-reel-panned');
} else { // goto slide 1
panzoom.pan(0,0,{animate:true});
setTimeout(function(){
gal_shift_prev();
}, gal_vars_pan_duration);
$('#gal-reel-prev').addClass('gal-reel-panned');
}

}

function gal_resize_slow(){
if(jQuery('.gal-crop-ctr').length==1){
gal_rcrop_reApply();
}

gal_orientation();
gal_screen_rotated();

if($('.gal-thumbs-ctr').css('display')!='none'){
gal_thumbs_UI();
$('.gal-thumbs-ctr').show();
}
new flexImages({selector: '.gal', rowHeight: 150});
$('.album-title').html($(window).width()+'x'+$(window).height());

if($('#album-notes-block').css('display')=='block'){
album_notes_readmore();
}

}



function gal_ready_settings(){

var xbright = localStorage.getItem('gal_brightness');
if(xbright !== null){
gal_vars_brightness = xbright;
}



var xanim = localStorage.getItem('gal_setting_animation');
if(xanim !== null){
gal_slide_animation = xanim;
}

var xtheme = localStorage.getItem('gal_setting_theme');
if(xtheme !== null){
gal_vars_theme = xtheme;
}

var xbutton = localStorage.getItem('gal_setting_buttons');
if(xbutton !== null){
gal_vars_button_set = xbutton;
}

var xtags = localStorage.getItem('gal_setting_tags');
if(xtags !== null){
gal_vars_tags = xtags;
}

var bgmode = localStorage.getItem('gal_setting_bgmode');
if(bgmode !== null){
gal_vars_bgmode = bgmode;
}

$('body').addClass('gal-theme-'+gal_vars_theme);
$('body').attr('data-theme', 'gal-theme-'+gal_vars_theme);
$('body').addClass('gal-buttons-'+gal_vars_button_set);
$('body').attr('data-buttons', 'gal-buttons-'+gal_vars_button_set);
$('body').addClass('gal-tags-'+gal_vars_tags);
$('body').attr('data-tags', 'gal-tags-'+gal_vars_tags);
$('body').addClass('gal-bgmode-'+gal_vars_bgmode);
$('body').attr('data-bgmode', 'gal-bgmode-'+gal_vars_bgmode);

$('body').addClass('animate-'+gal_slide_animation);
$('body').attr('data-anim', 'animate-'+gal_slide_animation);

// apply current button theme
$('body').append('<link id="gal-buttons-css-'+gal_vars_button_set+'" href="'+gal_domain+'phpix-imports/buttons/'+gal_vars_button_set+'/style.css" rel="stylesheet" type="text/css" />');

// apply current css theme
$('body').append('<link id="gal-theme-css-'+gal_vars_theme+'" href="'+gal_domain+'phpix-imports/themes/'+gal_vars_theme+'/'+gal_vars_bgmode+'.css" rel="stylesheet" type="text/css" />');

// apply current swipe animation
$('body').append('<link class="gal-swipe-css" id="gal-swipe-css-'+gal_slide_animation+'" href="'+gal_domain+'phpix-imports/animations/'+gal_slide_animation+'.css" rel="stylesheet" type="text/css" />');

// if single photo was prechosen to open
gal_preopen_gallery();
}



jQuery(document).ready(function($){

// debugbox for debugging
//$('body').append('<div class="dbug"></div>');


gal_ready_settings();


// preopen docs

if(getQueryString("tab")=="notes"){
setTimeout("album_notes()", 100);
}


if($('#flscrn').length==1){
gal_set_quality();
}

if(gal_vars_aid!=''){
gal_set_quality();
$(window).on("popstate", function(e) {
e.preventDefault();
$('.gal-prev').trigger('click');
});
}	

if($('.album-list').length!=0){
jQuery("body").on("click", ".album-out", function(e){
var xurl = $(this).find('.album-info > h2 > a').attr('href');
setTimeout(function(){
document.location.href = xurl;
}, 500);
});
}



//gal_demo();
//printExternal('http://localhost/family/original/(100).jpg');





jQuery(window).resize(function() {
clearTimeout(gal_resize_slow_timer);
gal_resize_slow_timer = setTimeout("gal_resize_slow()", 300);
}); 


jQuery("body").on("dblclick", "#gal-reel-now", function(){
start_zoom();
});

jQuery("body").on("dblclick", ".gal-zoomed", function(){
$('.gal-zoomed').remove();
});


// when expand icon is clicked
jQuery("body").on("click", ".gal-collapse, .gal-expand", function(){
var xtarget = $(this).closest('[xtarget]').attr('xtarget');
var xclass = $(this).attr('class');

if(xclass=='gal-collapse'){
$("."+xtarget).show();
$(this).attr('class', 'gal-expand');
localStorage.removeItem(xtarget);
localStorage.setItem(xtarget, 'gal-expand');
} else {
$("."+xtarget).hide();
$(this).attr('class', 'gal-collapse');
localStorage.removeItem(xtarget);
localStorage.setItem(xtarget, 'gal-collapse');
}

});

jQuery('body').on('keyup', null, 'left', function(){
if(jQuery('.gal-bg').length==1){
gal_shift_prev();
}
});

jQuery('body').on('keyup', null, 'right', function(){
if(jQuery('.gal-bg').length==1){
gal_shift_next();
}
});

jQuery('body').on('keyup', null, 'shift+z', function(){
if(jQuery('.gal-zoomed').length==0){
start_zoom();
} else {
gal_close_zoom();
}
});


jQuery('body').on('keyup', null, 'shift+i', function(){
if(jQuery('.gal-stats-ctr').length==1){
gal_picinfo();
}
});

jQuery('body').on('keyup', null, 'shift+r', function(){
if(jQuery('.gal-bg').length==1){
gal_rotate();
}
});

jQuery('body').on('keyup', null, 'shift+t', function(){
if(jQuery('.gal-bg').length==1){
gal_thumbnails();
}
});

jQuery('body').on('keyup', null, 'shift+space', function(){
if(jQuery('.gal-bg').length==1){
jQuery('.gal-play').trigger('click');
}
});

jQuery('body').on('keyup', null, 'shift+b', function(){
gal_brightness();
});

jQuery('body').on('keyup', null, 'shift+x', function(){
if(jQuery('#gal-reel-now .gal_loading').length==0 && jQuery('.gal-crop-ctr').length==0 && jQuery('.gal-progress-out').length==0){
mlib_rcrop_close();
var xurl = jQuery('.fullscreen-item > a > img').attr('src');
var xxurl = xurl.split('/thumb/');
gal_crop_photo_UI(xxurl[1]);
}
});

// when info pin is clicked
jQuery("body").on("click", ".gal-pin", function(){
jQuery('body').toggleClass('gal-stats-pinned');
});

// when next button is clicked
jQuery("body").on("click", ".gal-next", function(event){
panzoom.pan((-1)*($(window).width())*2,0);
setTimeout(function(){
gal_shift_next();
}, 100);
$('.gal-nav').hide();
});

// when prev button is clicked
jQuery("body").on("click", ".gal-prev", function(event){
panzoom.pan(0,0);
setTimeout(function(){
gal_shift_prev();
}, 100);
$('.gal-nav').hide();
});

// show fullscreen on clicking link
$('body').on('click', '.gal > li > a', function(e){
e.preventDefault();
$('.fullscreen-item').removeClass('fullscreen-item');
$(this).closest('li').addClass('fullscreen-item');
fullscreen_box();
});


// close the lightbox
$('body').on('click', '.gal-close', function(){
/*if(screenfull.enabled){
if (screenfull.isFullscreen) {
screenfull.exit();
}
}*/

if($('.gal-zoomed').length==0){
setTimeout("gal_remove_fullscreen_container()", 300);
} else {
gal_close_zoom();
}
});

// toggle the fullscreen mode
$('body').on('click', '.gal-fullscreen', function(){
toggleFullscreen('#flscrn');
});


// toggle the slideshow
$('body').on('click', '.gal-play', function(){
gal_prepare_slideshow();
});


// show video in fullscreen
$('body').on('click', '#gal-ext-app', function(){
var xurl = $(this).parent().find('img').attr('src');
var xurl2 = xurl.split('yt[');
var xurl3 = (xurl2[1]).split(']');
var ytid = xurl3[0];

var xhtml = '<div class="gal-ytframe"><div onclick="gal_ytframe_close()" class="gal-ytframe-close"></div><iframe src="https://www.youtube.com/embed/'+ytid+'?autoplay=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
$('#fullscreen').append(xhtml);
});


// show cursor when mouse moves, hide after 2 seconds
$('body').on('mousemove', '.gal-bg', function(){
$('.gal-hide-cursor').removeClass('gal-hide-cursor');
$('.gal-prev, .gal-next').fadeTo(10, 1);
clearTimeout(gal_hide_cursor);
gal_hide_cursor = setTimeout(gal_hide_cursor_now, 1000);
});

});


function gal_ytframe_close(){
$('.gal-ytframe').remove();
}


function gal_mobile_controls(xtype='toggle'){
if(xtype=='toggle'){
$('.gal-share, .gal-toolbar').toggleClass('gal-mobile-control');
}

if(xtype=='hide'){
$('.gal-share, .gal-toolbar').removeClass('gal-mobile-control');
}

if(xtype=='show'){
$('.gal-share, .gal-toolbar').addClass('gal-mobile-control');
}

}


function gal_orientation(){


if($(window).width()>=$(window).height()){
var new_gal_vars_orientation = 'gal-landscape';
} else {
var new_gal_vars_orientation = 'gal-potrait';
}

if(gal_vars_orientation!=new_gal_vars_orientation){
if(jQuery('.gal-crop-ctr').length==1){
var xw = parseInt((screen.width)*devicePixelRatio);
var xh = parseInt((screen.height)*devicePixelRatio);
$('#gal-crop-setting-width').val(xw);
$('#gal-crop-setting-height').val(xh);
gal_rcrop_reApply();
}
}

gal_vars_orientation = new_gal_vars_orientation;
$('body').removeClass('gal-landscape');
$('body').removeClass('gal-potrait');
$('body').addClass(gal_vars_orientation);
}


function gal_prepare_slideshow(){
if(jQuery('.gal-play').hasClass('gal-pause')){
gal_vars_slideshow = false;
gal_stop_slideshow();
} else {


var xhtml = '<p style="text-align:center;">How much time should each photo be displayed?</p><div class="gal-slide-timing">\
<a onclick="gal_slideshow_begins(3, this)">3 seconds</a></li>\
<a onclick="gal_slideshow_begins(4, this)">4 seconds</a>\
<a onclick="gal_slideshow_begins(5, this)">5 seconds</a>\
<a onclick="gal_slideshow_begins(6, this)">6 seconds</a>\
<a onclick="gal_slideshow_begins(7, this)">7 seconds</a>\
<a onclick="gal_slideshow_begins(8, this)">8 seconds</a>\
<a onclick="gal_slideshow_begins(9, this)">9 seconds</a>\
<a onclick="gal_slideshow_begins(10, this)">10 seconds</a>\
<a onclick="gal_slideshow_begins(15, this)">15 seconds</a>\
<a onclick="gal_slideshow_begins(20, this)">20 seconds</a>\
<a onclick="gal_slideshow_begins(30, this)">30 seconds</a>\
<a onclick="gal_slideshow_begins(60, this)">1 Minute</a>\
</div>';
phpl_alert(xhtml, 'Please choose...');

/*
gal_vars_slide_time = parseInt(prompt('How many seconds for each slide?', '5'));
if(gal_vars_slide_time>=2){
gal_vars_slideshow = true;
gal_start_slideshow();
} else if(!isNaN(gal_vars_slide_time)){
gal_toast('Number must be greater than 1. Please Retry.');
} else {
gal_toast('Only numbers are accepted. Please Retry.');
}
*/


}
}


function gal_slideshow_begins(xtime, xthis){
gal_vars_slide_time = xtime;
gal_vars_slideshow = true;
phpl_close_alert($(xthis).closest('.phpl-alert-ctr').attr('id'));
gal_mobile_controls('hide');
gal_start_slideshow();
}


function gal_start_slideshow(){
gal_toast('Slideshow begins!');
gal_preloader_UI();
jQuery('.gal-play').addClass('gal-pause');
jQuery('.gal-bg').append('<div class="gal-progress-out"><div class="gal-progress"></div></div>');
gal_slideshow();
}

function gal_stop_slideshow(){
gal_toast('Slideshow stopped!');
jQuery('.gal-preloader').remove();
jQuery('.gal-play').removeClass('gal-pause');
jQuery('.gal-progress-out').remove();
gal_vars_slideshow = false;
gal_vars_slide_time=5;
gal_vars_elapsed_time=0;
gal_vars_single_mode = false;
clearTimeout(gal_vars_slide_timer);
}


function gal_slideshow(){
if($('#gal-reel-now .gal_loading').length==0){

	if(gal_vars_elapsed_time==gal_vars_slide_time){
	gal_vars_elapsed_time = 0;
	jQuery('.gal-progress').css('width', '0%');
	gal_shift_next();
	} else {
	gal_vars_elapsed_time = gal_vars_elapsed_time + 1;
	var newWidth = parseFloat(gal_vars_elapsed_time/gal_vars_slide_time)*100;
	jQuery('.gal-progress').css('width', newWidth+'%');
	}

}

gal_vars_slide_timer = setTimeout("gal_slideshow()", 1000);
}

function gal_remove_fullscreen_container(){
jQuery('#fullscreen').remove();
$('.album-ctr, .album-bar-ctr, .gal-ctr, #album-sidebar').show();
jQuery('.no-scroll').removeClass('no-scroll');
gal_stop_slideshow();
// some bug causes window to be scaled again, hence reinitialized to fix it
//new flexImages({selector: '.gal', rowHeight: 150});
$('title').html('PHPix gallery');
window.history.pushState( {} , 'phpix', gal_domain+'phpix-alt.php?a='+gal_vars_aid );
}

// hide the cursor using css class
function gal_hide_cursor_now(){
$('.gal-prev, .gal-next, .gal-bg, .gal-hd-img, #gal-reel-now').addClass('gal-hide-cursor');
$('.gal-prev, .gal-next').fadeTo(10, 0.01);
}


function gal_download_options(xurl){
var xhtml = '<table class="phpl-alert-table">\
<tr><td><b>ORIGINAL</b> Maximum resolution</td><td id="gal-link-full"><a onclick="return gal_prepare_download(this)" data-q="full" href="'+gal_domain+'phpix-download.php?q=full&f='+xurl+'" download="full-'+xurl+'">Create Download</a></td></tr>\
<tr><td><b>FULL HD</b> 1080 pixels</td><td id="gal-link-fhd"><a onclick="return gal_prepare_download(this)" data-q="fhd" href="'+gal_domain+'phpix-download.php?q=fhd&f='+xurl+'" download="fhd-'+xurl+'">Create Download</a></td></tr>\
<tr><td><b>HD 720p</b> 720 pixels</td><td id="gal-link-hd"><a onclick="return gal_prepare_download(this)" data-q="hd" href="'+gal_domain+'phpix-download.php?q=hd&f='+xurl+'" download="hd-'+xurl+'">Create Download</a></td></tr>\
<tr><td><b>QHD 480p</b> 480 pixels</td><td id="gal-link-qhd"><a onclick="return gal_prepare_download(this)" data-q="qhd" href="'+gal_domain+'phpix-download.php?q=qhd&f='+xurl+'" download="qhd-'+xurl+'">Create Download</a></td></tr>\
</table>\
<p style="text-align:center;margin:10px auto 0 auto;">For custom size and cropping, <a href="javascript:void(0)" onclick="gal_crop_photo_UI(\''+xurl+'\')">click here</a></p>';
phpl_alert(xhtml, 'Download options');
}


function gal_set_output_quality(){
var xval = $('#gal-crop-setting-quality').val();
gal_rrcop_quality = parseFloat(xval);
jQuery('.gal-setting-cropped-pic').html('<p>You have changed a setting, please press crop button to generate the image.</p>');
}


function mlib_rcrop_toggle(){
$('body').toggleClass('gal-rcrop-toggle-body');
}



function gal_crop_photo_UI(xurl, xwidth = screen.width, xheight = screen.height, xaspect = 1){
phpl_close_alert();
if(xaspect==0){
var xaspectChk = '';
} else {
var xaspectChk = 'checked';
}

$('.fullscreen').append('<div class="gal-crop-ctr"><div class="gal-crop-bg"></div>\
<div class="gal-crop-area"><img id="gal-crop" onload="gal_init_rcrop()" src="'+gal_domain+'full/'+xurl+'"></div><div id="gal-crop-settings" class="gal-crop-settings">\
<h3 style="margin:0;">Crop Options</h3>\
<p><input '+xaspectChk+' type="checkbox" onchange="gal_switch_screen_options()" id="gal-crop-setting-aspect" value="true"> Fixed Screen size</p>\
<div id="gal-crop-settings-res" class="gal-crop-settings-box">\
<div class="gal-wide-50">Width : <input onkeyup="gal_rcrop_reApply()" onkeypress="return isNumber(event)" type="text" id="gal-crop-setting-width" value="'+(xwidth*devicePixelRatio)+'"></div>\
<div class="gal-wide-50">Height : <input onkeyup="gal_rcrop_reApply()" onkeypress="return isNumber(event)" type="text" id="gal-crop-setting-height" value="'+(xheight*devicePixelRatio)+'"></div>\
</div>\
<div id="gal-crop-settings-flip" class="gal-crop-settings-box">\
<input onclick="gal_flip_preview()" checked type="radio" name="gal-crop-settings-flip" value="v"> Flip Vertically<br>\
<input onclick="gal_flip_preview()" type="radio" name="gal-crop-settings-flip" value="h"> Flip Horizontally<br>\
<input onclick="gal_flip_preview()" type="radio" name="gal-crop-settings-flip" value="both"> Flip Both<br>\
<a onclick="gal_rcrop_flip()">Apply</a>\
</div>\
<div class="gal-crop-settings-box" id="gal-crop-settings-rotate-ctr"><input oninput="gal_crop_rotate_range(this.value)" style="width:100%;" type="range" min="0" max="360" value="0" id="gal-crop-settings-rotate"><span>Rotate : 0&deg;</span><a onclick="gal_rcrop_rotate()">Apply</a></div>\
<div class="gal-crop-settings-box">Output quality : <select onchange="gal_set_output_quality()" name="gal-crop-setting-quality" id="gal-crop-setting-quality">\
<option value="1">Best Quality - highest File size</option>\
<option selected value="0.9">Good Quality - small file size (Recommended)</option>\
<option value="0.5">Medium Quality - very small file size</option>\
<option value="0.1">Lowest Quality - smallest file size</option>\
</select></div>\
<p><input type="hidden" id="gal-crop-setting-picurl" value="'+xurl+'"></p>\
<p><input type="button" class="gal-btn blue" id="gal-crop-setting-cropnow" onclick="gal_rcrop_apply()" value="Crop Now"></p>\
<div class="gal-setting-cropped-pic"></div>\
</div>\
<ul class="gal-crop-toggles"><li id="gal-crop-toggle" onclick="mlib_rcrop_toggle()"></li><li id="gal-crop-exit" onclick="mlib_rcrop_close()"></li></ul>\
</div>');

if(gal_vars_orientation=='gal-potrait'){
gal_toast('For best experiance, rotate your device.');
}

$('.phpl-alert-ctr, .gal-bg').addClass('gal-crop-blur');
}


function gal_crop_rotate_range(xval){
clearTimeout(gal_vars_rotate_preview_timer);
$('#gal-crop-settings-rotate-ctr span').html('Rotate : '+xval+'&deg;');
$('#gal-crop').css('transform', 'rotate('+xval+'deg)');
gal_toast('Rotate preview...');
gal_vars_rotate_preview_timer = setTimeout('gal_crop_css_reset()', 4000);
}

function gal_flip_preview(){
clearTimeout(gal_vars_rotate_preview_timer);
var cbox = jQuery('[name="gal-crop-settings-flip"]:checked').val();
$('#gal-crop').css('transition', 'transform 1s');
if(cbox=='v'){
$('#gal-crop').css('transform', 'scaleY(-1)');
}

if(cbox=='h'){
$('#gal-crop').css('transform', 'scaleX(-1)');
}

if(cbox=='both'){
$('#gal-crop').css('transform', 'scaleX(-1) scaleY(-1)');
}
gal_toast('Flip preview...');
gal_vars_rotate_preview_timer = setTimeout('gal_crop_css_reset()', 4000);
}

function gal_crop_css_reset(){
$('#gal-crop').css('transform', '');
$('#gal-crop').css('transition', '');
}

function gal_rcrop_rotate(){
gal_toast('Rotating. Please wait...');
var degrees = parseInt($('#gal-crop-settings-rotate-ctr > input[type="range"]').val());
$('.gal-crop-settings').hide();
$('#gal-crop').attr('onload', 'gal_rcrop_reApply();');
const imageTag = document.getElementById('gal-crop');

rotate(imageTag.attributes.src.value, degrees, function(resultBase64) {

var newURL = blobify(resultBase64);
$('#gal-crop').attr('src', newURL);
$('#gal-crop-settings-rotate-ctr > input[type="range"]').val('0');
$('#gal-crop-settings-rotate-ctr span').html('Rotate : 0&deg;');
});

}


function gal_rcrop_flip(){
gal_toast('Flipping. Please wait...');
$('.gal-crop-settings').hide();
$('#gal-crop').attr('onload', 'gal_rcrop_reApply();');
const imageTag = document.getElementById('gal-crop');

var cbox = jQuery('[name="gal-crop-settings-flip"]:checked').val();
if(cbox=='v'){
var flipH = 0;
var flipV = 1;
} else if(cbox=='h'){
var flipH = 1;
var flipV = 0;
} else if(cbox=='both') {
var flipH = 1;
var flipV = 1;
} else {
var flipH = 0;
var flipV = 0;
}

flipImage(imageTag.attributes.src.value, flipH, flipV, function(resultBase64) {

var newURL = blobify(resultBase64);
$('#gal-crop').attr('src', newURL);
});

}



function flipImage(srcBase64, flipH, flipV, callback) {

  const canvas = document.createElement('canvas');
  let ctx = canvas.getContext("2d");
  let img = new Image();
  
    img.onload = function () {
	canvas.width = img.width;
	canvas.height = img.height;
	
	    var scaleH = flipH ? -1 : 1, // Set horizontal scale to -1 if flip horizontal
        scaleV = flipV ? -1 : 1, // Set verical scale to -1 if flip vertical
        posX = flipH ? img.width * -1 : 0, // Set x position to -100% if flip horizontal 
        posY = flipV ? img.height * -1 : 0; // Set y position to -100% if flip vertical
	
    ctx.save(); // Save the current state
    ctx.scale(scaleH, scaleV); // Set scale to flip the image
    ctx.drawImage(img, posX, posY, img.width, img.height); // draw the image
    ctx.restore(); // Restore the last saved state
	callback(canvas.toDataURL());
	}
img.src = srcBase64;
};


function rotate(srcBase64, degrees, callback) {
  const canvas = document.createElement('canvas');
  let ctx = canvas.getContext("2d");
  let image = new Image();

  image.onload = function () {
	if(image.width != image.height){
	var box_dimention = parseInt(Math.sqrt(image.width*image.width + image.height*image.height));
	} else {
	var box_dimention = image.width;
	}

    canvas.width = box_dimention;
    canvas.height = box_dimention;

    ctx.translate(canvas.width / 2, canvas.height / 2);
    ctx.rotate(degrees * Math.PI / 180);
    ctx.drawImage(image, image.width / -2, image.height / -2);

    callback(canvas.toDataURL());
  };

  image.src = srcBase64;
}

function gal_switch_screen_options(){
if(jQuery('#gal-crop-setting-aspect:checked').length==0){
jQuery('#gal-crop-settings-res').hide();
} else {
jQuery('#gal-crop-settings-res').show();
}

gal_rcrop_reApply();
}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function gal_rcrop_reApply(){
$('.gal-crop-settings').show();
$('#gal-crop').removeAttr('onload');
var xurl = $('#gal-crop-setting-picurl').val();
$('#gal-crop').rcrop('destroy');
$('.rcrop-preview-wrapper').remove();
gal_init_rcrop(xurl);
gal_set_output_quality();
}

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}


function scrollSmoothToBottom (id) {
   var div = document.getElementById(id);
   $('#' + id).animate({
      scrollTop: div.scrollHeight - div.clientHeight
   }, 500);
}

function gal_rcrop_apply(){
gal_toast('Cropping. Please wait...');
var deviceWidth = parseFloat(jQuery('#gal-crop-setting-width').val());
var deviceHeight = parseFloat(jQuery('#gal-crop-setting-height').val());
var lockAspect = jQuery('#gal-crop-setting-aspect:checked').length;
if(lockAspect==0){
var srcOriginal = $('#gal-crop').rcrop('getDataURL');
} else {
var srcOriginal = $('#gal-crop').rcrop('getDataURL', deviceWidth, deviceHeight);
}

var xthumb = $('.fullscreen-item > a > img').attr('src');
var uri = xthumb.split('/thumb/');

$('.gal-setting-cropped-pic').html('<p>'+formatBytes(srcOriginal.length/1.33)+' approx</p><a class="gal-btn green" href="'+blobify(srcOriginal)+'" download="custom-'+uri[1]+'">DOWNLOAD</a><img src="'+srcOriginal+'">');
gal_toast('Cropped successfully');
setTimeout(function(){
scrollSmoothToBottom('gal-crop-settings');
}, 1000);
}

function blobify(srcOriginal){
const base64ImageData = srcOriginal;
const contentType = 'image/jpeg';

//const byteCharacters = atob(base64ImageData.substr(`data:${contentType};base64,`.length));
const byteCharacters = atob(base64ImageData.replace(/^data:image\/(png|jpeg|jpg);base64,/, ''));
const byteArrays = [];

for (let offset = 0; offset < byteCharacters.length; offset += 1024) {
    const slice = byteCharacters.slice(offset, offset + 1024);

    const byteNumbers = new Array(slice.length);
    for (let i = 0; i < slice.length; i++) {
        byteNumbers[i] = slice.charCodeAt(i);
    }

    const byteArray = new Uint8Array(byteNumbers);

    byteArrays.push(byteArray);
}
const blob = new Blob(byteArrays, {type: contentType});
const blobUrl = URL.createObjectURL(blob);

return blobUrl;
}


function mlib_rcrop_close(){
$('#gal-crop').rcrop('destroy');
$('.gal-crop-blur').removeClass('gal-crop-blur');
$('.gal-crop-ctr').remove();
}

function gal_init_rcrop(xurl){

// device resolution

var deviceWidth = parseFloat(jQuery('#gal-crop-setting-width').val());
var deviceHeight = parseFloat(jQuery('#gal-crop-setting-height').val());
var aspectRatio = deviceWidth/deviceHeight;
var minWidth = aspectRatio*100;
var minHeight = 1*100;

var windowWidth = window.innerWidth-20;
var windowHeight = window.innerHeight-20;

$('#gal-crop').css('max-width', windowWidth+'px');
$('#gal-crop').css('max-height', windowHeight+'px');
$('#gal-crop').removeAttr('onload');

var lockAspect = jQuery('#gal-crop-setting-aspect:checked').length;
if(lockAspect==0){

$('#gal-crop').rcrop({
	grid:true,
	minSize:[minWidth,minHeight],
	preserveAspectRatio:false,
	preview: {
        display: true,
        size : [minWidth,minHeight],
    }
	});

} else {

$('#gal-crop').rcrop({
	grid:true,
	minSize:[minWidth,minHeight],
	preserveAspectRatio:true,
	preview: {
        display: true,
        size : [minWidth,minHeight],
    }
	});
	
}
	
}


function gal_prepare_download(xthis){
var xurl = $(xthis).attr('href');
var xParams = new URLSearchParams(xurl);
var xq = $(xthis).attr('data-q');
var ghtml = $(xthis).parent().html();
var gid = $(xthis).parent().attr('id');
$('#'+gid).html('<b>Processing...</b>');
$.get( xurl ,function(data) {
$('#'+gid).html(ghtml);
var newURL = gal_domain+''+xq+'/'+xParams.get('f');
$('#'+gid+' > a').attr('href', newURL);
$('#'+gid+' > a').removeAttr('onclick');
$('#'+gid+' > a').html('Download Now!');
$('#'+gid+' > a').addClass('gal-download-ready');
});
return false;
}

// adding needed markup
function gal_init_startup(){
	
jQuery( ".gal" ).each(function(i) {
var xitems = $('.gal > li').length;
$(this).attr('data-items', xitems);
jQuery(this).find('li').each(function(i) {
var cid = 'gal-item-'+i;
jQuery(this).attr('data-count',i);
jQuery(this).attr('id',cid);
});

});

// single preopen photo
if(gal_vars_single_mode===true){
var xurl = gal_domain+''+gal_quality+'/'+gal_vars_single_photo;
$('a[href="'+xurl+'"]').trigger('click');
}

}


function gal_preopen_gallery(){
var xaid = getQueryString("aid");
var xpic = getQueryString("pic");
if(xaid !== null){

var aid_arr = xaid.split('.');
var aid_tot = aid_arr.length;

if(aid_tot==1){xaid = aid_arr[0];} 

if(aid_tot==2){
xaid = aid_arr[1];
}

if(aid_tot>2){
var aid_tot_int = aid_tot - 1;
xnaid = aid_arr[aid_tot_int];
gal_vars_remainingURL = xaid.replace('.'+xnaid, '');
xaid = xnaid;
}

if(xpic !== null){ // if aid exists and pic exists
gal_vars_single_mode = true;
gal_vars_single_photo = xpic;
} else { // aid exists but pic does not
gal_vars_single_mode = false;
}

$('.album-list b[xurl="'+xaid+'"]').trigger('click');

}
}

// hide zoom leavel info
function gal_hide_zoom_lvl_func(){
jQuery('.zoomIn>.description').hide();
}


function album_about(){
var xhtml = '<p><b>PHPix Gallery</b> is a free and open source software maintained by <a target="_blank" href="http://phploaded.com/project/phpix.html">PHPloaded.com</a> and developed by <a rel="nofollow" target="_blank" href="https://www.facebook.com/sakush100/">Satish Kumar Sharma</a>.<br /><br />Its purpose is to organise personal photo online, without giving away your photos to third party websites and social media platforms. This gives you instant access to your photos anytime, anywhere and any device with just a web browser. This helps to safeguard your photos and have complete control in your hands. <br /><br />This software can be run on self hosted PHP and MySQL server, even on shared linux hosting, very easily.<br /><br /><a target="_blank" href="http://phploaded.com/project/phpix.html">PHPix gallery</a> is released under <b><a rel="nofollow" target="_blank" href="https://raw.githubusercontent.com/phploaded/PHPix/master/LICENSE">MIT licence</a></b>.</p>';
phpl_alert(xhtml, 'About PHPix Gallery');
}

// used during slideshow
function gal_preloader_error(){
//gal_stop_slideshow();
$('.gal-preloader > img').attr('src', gal_domain+'css/svg/error.svg');
//gal_toast('Next photo failed! Slideshow aborted!!');
}

// used during single image loading
function gal_preload_failed(xthis){
gal_toast('Loading failed!!');
var xurl = gal_domain+'css/svg/error.svg';
$('.gal-hd-img').attr('src', xurl);
jQuery('.gal-hd-img').removeClass('blurred');
$('.gal-nav').show();
gal_sharer(xurl);
gal_reset_navigation();

gal_ajax_exif(xurl);
}


function gal_preloader_UI(){
var cid = parseInt(jQuery('.fullscreen-item').attr('data-count'));
var maxid = parseInt(jQuery('.fullscreen-item').closest('[data-items]').attr('data-items'));

// next id in slideshow
if(cid==(maxid-1)){
var nid = 0;
} else {
var nid = cid + 1;
}

var nextSRC = jQuery('#gal-item-'+nid+' > a').attr('href');

jQuery('.gal-bg').append('<div class="gal-preloader"><img onerror="gal_preloader_error()" onload="gal_preloader_ready()" src="'+nextSRC+'"></div>');
}


function gal_preloader_ready(){
jQuery('.gal-preloader').addClass('gal-preloader-ready');
}

function gal_reel_replace(xsel){
var xid = $(xsel+' img').attr('data-id');
var ximg = $('#gal-item-'+xid+' img').attr('src');
var xfull = ximg.replace('/thumb/', '/'+gal_quality+'/');
$(xsel+' img').removeAttr('onload');
$(xsel+' img').attr('src', xfull);
}


function gal_doubletap_zoom(){
    if (gal_vars_tapTimer == null) {
        gal_vars_tapTimer = setTimeout(function () {
            gal_vars_tapTimer = null;
        //    alert("single");

        }, 500)
    } else {
        clearTimeout(gal_vars_tapTimer);
        gal_vars_tapTimer = null;
    //    alert("double");
	start_zoom();

    }
}


function testx(xthis){
//alert($(xthis).attr('data-id'));
}

// create fullscreen UI and display clicked photo
function fullscreen_box(){

if (window.panzoom2 !== undefined){
panzoom2.destroy();
delete window.panzoom2;
}

if (window.panzoom !== undefined){
panzoom.destroy();
delete window.panzoom;
}

$('.album-ctr, .album-bar-ctr, .gal-ctr, #album-info, #album-sidebar').hide();

var xthumb = $('.fullscreen-item > a > img').attr('xsrc');
var xphoto = $('.fullscreen-item > a').attr('href');
var xtype = $('.fullscreen-item').attr('data-xtype');

var cid = parseInt($('.fullscreen-item').attr('data-count'));
var maxid = parseInt($('.fullscreen-item').closest('[data-items]').attr('data-items'));

var prev_id = cid - 1;
if(cid==0){prev_id = maxid-1;}

var next_id = cid + 1;
if(cid==(maxid-1)){next_id = 0;}


var prev_pic = $('#gal-item-'+prev_id+' a').attr('href');
var next_pic = $('#gal-item-'+next_id+' a').attr('href');
var prev_thumb = $('#gal-item-'+prev_id+' img').attr('xsrc');
var next_thumb = $('#gal-item-'+next_id+' img').attr('xsrc');

var basic_html = '<div id="gal-reel-ctr">\
<div id="gal-reel">\
<div id="gal-reel-prev"><div id="gal-reel-prev-inner"><img style="'+gal_get_css(prev_id)+'" data-id="'+prev_id+'" src="'+prev_thumb+'" /><div class="gal_loading"></div></div></div>\
<div class="gal-reel-panned" id="gal-reel-now"><div id="gal-reel-now-inner"><img style="'+gal_get_css(cid)+'" data-id="'+cid+'" id="gal-hd-img" ontouchmove="gal_touch_controls()" class="gal-hd-img blurred" alt="loading..." src="'+xthumb+'" /><div class="gal_loading"></div><div id="gal-ext-app" class="gal-xtype-'+xtype+'"></div></div></div>\
<div id="gal-reel-next"><div id="gal-reel-next-inner"><img style="'+gal_get_css(next_id)+'" data-id="'+next_id+'" src="'+next_thumb+'" /><div class="gal_loading"></div></div></div>\
</div>\
</div>\
<div class="gal-reel-preloader">\
<img onload="gal_preload_complete(this)" src="'+prev_pic+'" />\
<img onload="gal_preload_complete(this)" src="'+next_pic+'" />\
</div>';

var aid = getQueryString('aid');
var uri = xphoto.split('/'+gal_quality+'/');
$('title').html('PHPix photo - '+uri[1]);
window.history.pushState( {} , 'phpix', gal_domain+'phpix-alt.php?u='+uri[1] );

if(jQuery('.fullscreen').length==1){

$('.gal-zoomed').remove();

$('#gal-reel-out').html(basic_html);


jQuery('.gal-preload').attr('src', xphoto);
jQuery('.gal-stats, .gal-share').html('<div class="gal_loading"></div>');

} else {

var xval = localStorage.getItem('gal_cart_json');
if(xval === null){ // if null
var total = 0;
} else {
var xjson = jQuery.parseJSON(xval);
var total = parseInt(xjson.xtot);
}

jQuery('#album-pics-block').append('<div id="fullscreen" class="fullscreen">\
<div class="fullscreen-css"></div>\
<div class="fullscreen-bg"></div>\
<div ontouchmove="gal_touch_controls()" class="gal-bg">\
<div class="zoomed-in-text"></div>\
<div class="gal-counter"></div>\
<div class="gal-share-ctr"><div class="gal-share"><div class="gal_loading"></div></div></div>\
<div class="gal-stats-ctr"><div onclick="gal_picinfo()" class="gal-picinfo-close">X</div><h2>Photo Information</h2><div id="gal_stats" class="gal-stats"><div class="gal_loading"></div></div></div>\
<ul class="gal-toolbar"><li class="gal-play"></li><li onclick="gal_rotate()" class="gal-rotate"></li><li onclick="gal_fitscreen()" class="gal-screenfit"></li><li class="gal-fullscreen"></li><li class="gal-close"></li></ul>\
<div id="gal-reel-out">'+basic_html+'</div>\
<div class="gal-thumbs-ctr"></div>\
<div class="gal-prev"></div>\
<div class="gal-next"></div>\
<div data-items="'+total+'" onclick="gal_show_cart()" class="gal-cart-indicator"><b>'+total+'</b></div>\
<div  onclick="gal_mobile_controls()" class="gal-mobile-controls"></div>\
<img onerror="gal_preload_failed(this)" onload="gal_preloaded(this)" id="gal_preloaded" class="gal-preload" src="'+xphoto+'" /></div></div>');
jQuery('body').addClass('no-scroll');

// load value from cache
var xval = localStorage.getItem('gal_exif');
if(xval !== null && xval!='no'){ // if null
$('body').addClass('gal-stats-pinned');
}

// load thumbnails if cache value is yes
var xval = localStorage.getItem('gal_setting_thumbnails');
if(xval !== null){ // if null
gal_thumbnails(xval);
}

if(gal_vars_single_mode===true){
gal_init_fullscreen = false;
gal_toast('For fullscreen, move cursor at top-right corner');
} else {
if(gal_init_fullscreen===true){
//openFullscreen();
}
}

}

gal_screen_rotated();
gal_demo();


gal_xtag_reinit();



// if pause button is visible, create preloader UI
jQuery('.gal-preloader').remove();
if(jQuery('.gal-play').hasClass('gal-pause')){
gal_preloader_UI();
}

gal_set_thumb();

}


function gal_getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function gal_random_animation(){
var anim = gal_slide_animations;
// to remove array element by value
anim = anim.filter(val => val !== "plain");
anim = anim.filter(val => val !== "any-random");
var xkey = gal_getRandomInt(0, (anim.length-1));
var newanim = 'animate-'+anim[xkey];
var xdata = $('body').attr('data-anim');
$('body').attr('data-anim', newanim);
$('body').removeClass(xdata).addClass(newanim);

$('.gal-swipe-css').remove();
$('body').append('<link class="gal-swipe-css" id="gal-swipe-css-'+anim[xkey]+'" href="'+gal_domain+'phpix-imports/animations/'+anim[xkey]+'.css" rel="stylesheet" type="text/css" />');

}

function gal_switch_animation(xval){
var xdata = $('body').attr('data-anim');
$('body').attr('data-anim', 'animate-'+xval);
$('body').removeClass(xdata).addClass('animate-'+xval);
}


function gal_settings(){
album_toggle_sidebar();
var thtml = '';
for(var i=0;i<gal_vars_themes.length;++i){
thtml = thtml + '<option value="'+gal_vars_themes[i]+'">'+(i+1)+'. '+gal_vars_themes[i]+'</option>';
}


var bhtml = '';
for(var i=0;i<gal_vars_button_sets.length;++i){
bhtml = bhtml + '<option value="'+gal_vars_button_sets[i]+'">'+(i+1)+'. '+gal_vars_button_sets[i]+'</option>';
}


var anim = gal_slide_animations;

var ahtml = '<select id="gal-setting-animation" name="gal-setting-animation">';
for(var i=0;i<anim.length;++i){
ahtml = ahtml + '<option value="'+anim[i]+'">'+(i+1)+'. '+anim[i]+'</option>';
}
ahtml = ahtml + '</select>';

var xhtml = '<form name="gal-settings-form"><table class="phpl-alert-table"><tbody>\
<tr><td>Swipe Animation</td><td>'+ahtml+'</td></tr>\
<tr><td>Interface Theme</td><td><select id="gal-setting-theme" name="gal-setting-theme">'+thtml+'</select></td></tr>\
<tr><td>Button Set</td><td><select id="gal-setting-buttons" name="gal-setting-buttons">'+bhtml+'</select></td></tr>\
<tr><td>Photo Quality</td><td><select id="gal-setting-pixq" name="gal-setting-pixq"><option value="automatic">Automatic</option><option value="full">Original</option><option value="fhd">Full HD 1080p</option><option value="hd">HD 720p</option><option value="qhd">Quarter HD</option></select></td></tr>\
<tr><td>Photo Tags</td><td><select id="gal-setting-tags" name="gal-setting-tags"><option value="show">show</option><option value="hide">hide</option></select></td></tr>\
<tr><td>Background</td><td><select id="gal-setting-bgmode" name="gal-setting-bgmode"><option value="static">static</option><option value="animated">animated</option><option value="disabled">disabled</option></select></td></tr>\
<tr><td>Thumbnails</td><td><select id="gal-setting-thumbs" name="gal-setting-thumbs"><option value="show">show</option><option value="hide">hide</option></select></td></tr>\
</tbody></table>\
<p style="margin-top:5px;"><a href="javascript:void(0)" onclick="gal_brightness()">Adjust Brightness</a> (Helps with dark photos)</p><p style="margin-top:5px;">Pressing <b>defaults</b> button only loads default values, you must press <b>save &amp; apply</b> to save changes!\
</p>\
</form>';

var xfooter = '<div class="phpl-alert-btn-danger phpl-alert-close">Close</div><div onclick="gal_settings_reset()" class="phpl-alert-btn-warning">Defaults</div><div onclick="gal_settings_apply()" class="phpl-alert-btn-success">Save &amp; Apply</div>';

phpl_alert(xhtml, 'Choose settings...', xfooter);

gal_selectbox('gal-setting-animation');
gal_selectbox('gal-setting-thumbs');
gal_selectbox('gal-setting-buttons');
gal_selectbox('gal-setting-tags');
gal_selectbox('gal-setting-theme');
gal_selectbox('gal-setting-bgmode');
gal_selectbox('gal-setting-pixq');

// apply live values
var xtheme = ($('body').attr('data-theme')).replace('gal-theme-', '');
$('#gal-setting-theme').val(xtheme).trigger('change');

var xbutton = ($('body').attr('data-buttons')).replace('gal-buttons-', '');
$('#gal-setting-buttons').val(xbutton).trigger('change');

var xtag = ($('body').attr('data-tags')).replace('gal-tags-', '');
$('#gal-setting-tags').val(xtag).trigger('change');

var xthumb = localStorage.getItem('gal_setting_thumbnails');
if(xthumb===null){xthumb=gal_vars_mini_thumbs;}
$('#gal-setting-thumbs').val(xthumb).trigger('change');

//var xanim = ($('body').attr('data-anim')).replace('animate-', '');
$('#gal-setting-animation').val(gal_slide_animation).trigger('change');

var xbgmode = ($('body').attr('data-bgmode')).replace('gal-bgmode-', '');
$('#gal-setting-bgmode').val(xbgmode).trigger('change');

$('#gal-setting-pixq').val(gal_pixq).trigger('change');

}



function gal_selectbox(sel){
var cval = $('#'+sel).val();
var ctext = $('#'+sel+' [value="'+cval+'"]:first-child').html();
$('#'+sel).addClass('gal-selectbox-original');
$('#'+sel).attr('onchange', 'gal_selectbox_update(this)');
$('#'+sel).attr('data-boxid', sel);
$('#'+sel).wrap(function(){
return '<div class="gal-selectbox" id="gal-selectbox-'+sel+'"></div>';
});

var xhtml = '<span onclick="gal_selectbox_open(this)">'+ctext+'</span><ul onclick="gal_selectbox_val(event, this)">';
$('#'+sel+' option').each(function() {
xhtml = xhtml + '<li data-val="'+$(this).val()+'">'+$(this).text()+'</li>';
});

xhtml = xhtml + '</ul>';

$('#gal-selectbox-'+sel).append(xhtml);
$('#gal-selectbox-'+sel+' li').removeClass('gal-selectbox-selected');
$('#gal-selectbox-'+sel+' li[data-val="'+cval+'"]').addClass('gal-selectbox-selected');
}



function gal_selectbox_open(xthis){
if($(xthis).closest('.gal-selectbox').hasClass('gal-selectbox-open')){
$('.gal-selectbox-open').removeClass('gal-selectbox-open');
$(xthis).closest('.gal-selectbox').removeClass('gal-selectbox-open');
} else {
$('.gal-selectbox-open').removeClass('gal-selectbox-open');
$(xthis).closest('.gal-selectbox').addClass('gal-selectbox-open');
}

}


function gal_selectbox_val(e, xthis){
var xval = $(e.target).attr('data-val');
var xtext = $(e.target).text();
//console.log(e.target);
$(e.target).closest('.gal-selectbox').find('select').val(xval);
$(e.target).closest('.gal-selectbox').find('select option').attr('checked', false);
$(e.target).closest('.gal-selectbox').find('select option[value="'+xval+'"]').attr('checked', true);
$('.gal-selectbox-open').removeClass('gal-selectbox-open');
$(e.target).closest('.gal-selectbox').find('select').trigger('change');
}

function gal_selectbox_update(xthis){
var xid = xthis.id;
var xtext = $('#'+xid+' option[value="'+xthis.value+'"]').text();
$('#gal-selectbox-'+xid+' span').html(xtext);
$('#gal-selectbox-'+xid+' li').removeClass('gal-selectbox-selected');
$('#gal-selectbox-'+xid+' li[data-val="'+xthis.value+'"]').addClass('gal-selectbox-selected');
}


function gal_settings_apply(){

// animation
var xanim = $('#gal-setting-animation').val();
$('#gal-swipe-css-'+gal_slide_animation).remove();
$('body').removeClass('animate-'+oldbgmode).addClass('animate-'+bgmode);
$('body').append('<link id="gal-swipe-css-'+xanim+'" href="'+gal_domain+'phpix-imports/animations/'+xanim+'.css" rel="stylesheet" type="text/css" />');
gal_switch_animation(xanim);
localStorage.setItem('gal_setting_animation', xanim);
gal_slide_animation = xanim;

// thumbnails
var xthumb = $('#gal-setting-thumbs').val();
gal_thumbnails(xthumb);


// bgmode
var bgmode = $('#gal-setting-bgmode').val();
var oldbgmode = $('body').attr('data-bgmode');
$('body').removeClass(oldbgmode).addClass('gal-bgmode-'+bgmode);
localStorage.setItem('gal_setting_bgmode', bgmode);
$('body').attr('data-bgmode', 'gal-bgmode-'+bgmode);

// main theme
var xtheme = $('#gal-setting-theme').val();
var oldtheme = ($('body').attr('data-theme')).replace('gal-theme-', '');
$('#gal-theme-css-'+oldtheme).remove();
$('body').append('<link id="gal-theme-css-'+xtheme+'" href="'+gal_domain+'phpix-imports/themes/'+xtheme+'/'+bgmode+'.css" rel="stylesheet" type="text/css" />');
$('body').removeClass('gal-theme-'+oldtheme).addClass('gal-theme-'+xtheme);
localStorage.setItem('gal_setting_theme', xtheme);
$('body').attr('data-theme', 'gal-theme-'+xtheme);

// button theme
var buttons = $('#gal-setting-buttons').val();
var oldbuttons = ($('body').attr('data-buttons')).replace('gal-buttons-', '');
$('#gal-buttons-css-'+oldbuttons).remove();
$('body').append('<link id="gal-buttons-css-'+buttons+'" href="'+gal_domain+'phpix-imports/buttons/'+buttons+'/style.css" rel="stylesheet" type="text/css" />');
$('body').removeClass('gal-buttons-'+oldbuttons).addClass('gal-buttons-'+buttons);
localStorage.setItem('gal_setting_buttons', buttons);
$('body').attr('data-buttons', 'gal-buttons-'+buttons);

// photo tags
var tags = $('#gal-setting-tags').val();
var oldtags = $('body').attr('data-tags');
$('body').removeClass(oldtags).addClass('gal-tags-'+tags);
localStorage.setItem('gal_setting_tags', tags);
$('body').attr('data-tags', 'gal-tags-'+tags);

// pixq
var pixq = $('#gal-setting-pixq').val();
localStorage.setItem('gal_setting_pixq', pixq);
gal_apply_pixq(pixq);


gal_mobile_controls('hide');
phpl_close_alert();
gal_toast('Changes applied!');
}


function gal_apply_pixq(pixq){
if(gal_pixq!=pixq){
phpl_alert('Sending your to homepage in 5 seconds because photo quality is changed! You can navigate to same album again!', 'quality changed');
setTimeout(function(){
document.location.href = gal_domain+''+albumFILE;
}, 5000);
}
gal_pixq = pixq;
gal_set_quality();
$('.gal .item').each(function() {
var xurl = $(this).find('img').attr('xsrc');
var nurl = xurl.replace('/thumb/', '/'+gal_quality+'/');
$(this).find('a').attr('href', nurl);
});

}

function gal_settings_reset(){
$('#gal-setting-animation').val($('#gal-setting-animation option:first-child').val());
$('#gal-setting-animation').trigger('change');
$('#gal-setting-thumbs').val($('#gal-setting-thumbs option:first-child').val());
$('#gal-setting-thumbs').trigger('change');
$('#gal-setting-theme').val($('#gal-setting-theme option:first-child').val());
$('#gal-setting-theme').trigger('change');
$('#gal-setting-buttons').val($('#gal-setting-buttons option:first-child').val());
$('#gal-setting-buttons').trigger('change');
$('#gal-setting-tags').val($('#gal-setting-tags option:first-child').val());
$('#gal-setting-tags').trigger('change');
$('#gal-setting-bgmode').val($('#gal-setting-bgmode option:first-child').val());
$('#gal-setting-bgmode').trigger('change');
$('#gal-setting-pixq').val($('#gal-setting-pixq option:first-child').val());
$('#gal-setting-pixq').trigger('change');
}


function gal_single_css(xselector){
var xitem = $(xselector).attr('data-id');
return gal_get_css(xitem);
}


function gal_thumbnails(xval = 'toggle'){ // show, hide, toggle
if($('.gal-thumbs').length==0 && xval != 'hide'){
gal_thumbs_UI();
$('.gal-thumbs-ctr').show();
localStorage.setItem('gal_setting_thumbnails', 'show');
} else {

if(xval=='toggle'){
	if($('.gal-thumbs-ctr').css('display')=='none'){
	$('.gal-thumbs-ctr').css('display', 'block');
	localStorage.setItem('gal_setting_thumbnails', 'show');
	} else {
	$('.gal-thumbs-ctr').css('display', 'none');
	localStorage.setItem('gal_setting_thumbnails', 'hide');
	}
} else {
	if(xval=='show'){
	$('.gal-thumbs-ctr').css('display', 'block');
	localStorage.setItem('gal_setting_thumbnails', 'show');
	} else {
	$('.gal-thumbs-ctr').css('display', 'none');
	localStorage.setItem('gal_setting_thumbnails', 'hide');
	}
}

}
gal_mobile_controls('hide');
}


function gal_thumbs_UI(){
$('.gal-thumbs-ctr').html('');
$('.gal-thumbs-ctr').removeAttr('style');
var maxid = parseInt($('.fullscreen-item').closest('[data-items]').attr('data-items'));
var maxw = maxid*gal_vars_thumbsize;
var xhtml = '<ul style="width:'+maxw+'px;" id="gal-thumbs" class="gal-thumbs">';

for(var i=0;i<maxid;++i){
var xthumb = $('#gal-item-'+i+' img').attr('xsrc');
xhtml = xhtml + '<li ontouchend="gal_shift_by_id(this)" onclick="gal_shift_by_id(this)" data-id="'+i+'" style="width:'+gal_vars_thumbsize+'px;height:'+gal_vars_thumbsize+'px;"><img src="'+xthumb+'"></li>';
}

xhtml = xhtml + '</ul>';
$('.gal-thumbs-ctr').html(xhtml);

gal_set_thumb();

var scrw = $(window).width();
$('.gal-thumbs-ctr').css('width', scrw+'px');

var elem = document.getElementById('gal-thumbs');
if($('#gal-thumbs').length==1){
	if(scrw<maxw){
		panzoomThumbs = Panzoom(elem, {
		maxScale: 1,
		minScale: 1,
		startScale: 1,
		contain: 'outside',
		animate: true,
		startX: 0,
		step: 0.1
		});
	} else {
		panzoomThumbs = Panzoom(elem, {
		maxScale: 1,
		minScale: 1,
		startScale: 1,
		contain: 'inside',
		animate: true,
		startX: 0,
		step: 0.1
		});
	}
}

}

function gal_set_thumb(){
$('.gal-thumb-selected').removeClass('gal-thumb-selected');
var cid = parseInt($('#gal-reel-now img').attr('data-id'));
$('.gal-thumbs li[data-id="'+cid+'"]').addClass('gal-thumb-selected');
}


function gal_shift_by_id(xthis){
var xid = $(xthis).attr('data-id');
$('#gal-item-'+xid+' a').trigger('click');
}


function isEven(n) {return parseInt(n)%2===0?true:parseInt(n)===0?true:false}


function gal_rotate(noRotate = 'off'){

var xid = $('#gal-reel-now img').attr('data-id');
var xh = parseInt($('#gal-item-'+xid).attr('data-h'));
var xw = parseInt($('#gal-item-'+xid).attr('data-w'));
var ph = $(window).height()
var pw = (xw/xh)*ph;
var rotation = parseInt($('#gal-reel-now img').attr('data-deg'));

if(isNaN(rotation)){
rotation = 0;
}

if(noRotate=='off'){
rotation = rotation + 90;
}

if(rotation==90 || rotation==270){ // if not even number
var xsize = gal_get_css_rotate(xid);
$('#xtag-element, #xtag-element img').attr('style', xsize);
$('#xtag-element').css('transform', 'translate(0px) rotate('+rotation+'deg)');
} else {
var xsize = gal_get_css(xid);
$('#xtag-element, #xtag-element img').attr('style', xsize);
$('#xtag-element').css('transform', 'translate(0px) rotate('+rotation+'deg)');
}

if(rotation==360){
setTimeout(function(){
$('#xtag-element').css('transition', 'transform 0s').css('transform', 'translate(0px) rotate(0deg)');
$('#gal-reel-now img').attr('data-deg', 0);
},500);
}


$('#gal-reel-now img').attr('data-deg', rotation);

}

function gal_get_cssx(xid){
return '';
}

function gal_get_cssxx(xid){
var windowHeight = $(window).height();
var windowWidth = $(window).width();
var thumb_height = parseInt($('#gal-item-'+xid).attr('data-h'));
var thumb_width = parseInt($('#gal-item-'+xid).attr('data-w'));
max_thumb_width = parseInt((windowWidth/windowHeight) * thumb_height) + 1;

if(thumb_width < max_thumb_width){
	if(gal_vars_expand=='disabled'){
	var xcss = 'height:100%;width:auto;';
	} else {
	var xcss = 'width:100%;height:auto;';
	}
} else {
	if(gal_vars_expand=='enabled'){
	var xcss = 'height:100%;width:auto;';
	} else {
	var xcss = 'width:100%;height:auto;';
	}
}
return xcss;
}


function gal_screenFit(srcWidth, srcHeight, maxWidth, maxHeight) {
var ratio = Math.min(maxWidth / srcWidth, maxHeight / srcHeight);
return { width: srcWidth*ratio, height: srcHeight*ratio };
}

function gal_screenFill(srcWidth, srcHeight, maxWidth, maxHeight) {
var ratio = Math.max(maxWidth / srcWidth, maxHeight / srcHeight);
return { width: srcWidth*ratio, height: srcHeight*ratio };
}

function gal_get_css(xid){
if($('.gal-stats-pinned').length==0){
var windowWidth = $(window).width();
} else {
var windowWidth = $(window).width()-300;
}
var windowHeight = $(window).height();
var thumbHeight = parseInt($('#gal-item-'+xid).attr('data-h'));
var thumbWidth = parseInt($('#gal-item-'+xid).attr('data-w'));
if($('.gal-zoomfit').length==1){
var xsize = gal_screenFill(thumbWidth, thumbHeight, windowWidth, windowHeight);
} else {
var xsize = gal_screenFit(thumbWidth, thumbHeight, windowWidth, windowHeight);
}
var xcss = 'height:'+parseInt(xsize.height)+'px;width:'+parseInt(xsize.width)+'px;';
return xcss;
}

function gal_get_css_rotate(xid){
var windowWidth = $(window).width();
var windowHeight = $(window).height();
var thumbHeight = parseInt($('#gal-item-'+xid).attr('data-w'));
var thumbWidth = parseInt($('#gal-item-'+xid).attr('data-h'));
if($('.gal-zoomfit').length==1){
var xsize = gal_screenFill(thumbWidth, thumbHeight, windowWidth, windowHeight);
} else {
var xsize = gal_screenFit(thumbWidth, thumbHeight, windowWidth, windowHeight);
}
var xcss = 'height:'+parseInt(xsize.width)+'px;width:'+parseInt(xsize.height)+'px;';
return xcss;
}


function gal_screen_rotated(){
	if($('.fullscreen').length==1){
		// based on thumbnail width, decide if landscape mode or potrait mode
		var windowHeight = $(window).height();
		var windowWidth = $(window).width();
		$('.fullscreen-css').html('<style type="text/css">#gal-reel-ctr{width:'+windowWidth+'px;height:'+windowHeight+'px;}\
		#gal-reel{width:'+(windowWidth*3)+'px;}\
		#gal-reel-prev, #gal-reel-now, #gal-reel-next{width:'+(windowWidth*1)+'px;}\
		#gal-reel-prev img{'+gal_single_css('#gal-reel-prev img')+'}\
		#gal-reel-next img{'+gal_single_css('#gal-reel-next img')+'}\
		.gal-hd-img{'+gal_single_css('.gal-hd-img')+'}</style>');
		$('#gal-reel-prev img').attr('style', gal_single_css('#gal-reel-prev img'));
		$('#gal-reel-next img').attr('style', gal_single_css('#gal-reel-next img'));
		$('#gal-reel-now img, #xtag-element').attr('style', gal_single_css('#gal-reel-now img'));
		gal_rotate('on');
	}

if (window.panzoom !== undefined){
panzoom.pan((-1)*windowWidth,0,{animate:false});
}



}

// preload clicked photo and show when ready
function gal_preloaded(xthis){
var xurl = $(xthis).attr('src');
$('.gal-hd-img').attr('src', xurl);
jQuery('.gal-hd-img').removeClass('blurred');
$('#gal-reel-now-inner .gal_loading').remove();
$('.gal-nav').show();
gal_sharer(xurl);
gal_reset_navigation();

gal_ajax_exif();

}

// exif reading
function gal_ajax_exif(){
if(jQuery('.gal-stats-pinned').length==1 && jQuery('.gal-stats .gal_loading').length==1){
var xurl = jQuery('.fullscreen-item > a > img').attr('xsrc');
var xxurl = xurl.replace('/thumb/', '/'+gal_quality+'/');
$.get( gal_domain+"phpix-exif.php", {id:xxurl} ,function(data) {
$('#gal_stats').html(data);
});
}
}

// runs element in fullscreen
function openFullscreen(xtar='#fullscreen') {
const target = $(xtar)[0];
if (screenfull.enabled) {
screenfull.request(target);
gal_init_fullscreen = true;
jQuery('.gal-fullscreen').addClass('gal-restore');
} else {
jQuery('.gal-fullscreen').hide();
}
}

// if browser supports fullscreen, exit fullscreen
function closeFullscreen(){
if (screenfull.enabled) {
screenfull.exit();
gal_init_fullscreen = false;
jQuery('.gal-fullscreen').removeClass('gal-restore');
} else {
jQuery('.gal-fullscreen').hide();
}
}

function toggleFullscreen(x){
if(screenfull.isFullscreen){
closeFullscreen(x);
} else {
openFullscreen(x);
}
gal_mobile_controls('hide');
}


function gal_gen_thumbs(){

var gal_progress_bar_current = $('#new_thumbs > li').length;
var pcent = 100-parseInt((gal_progress_bar_current/gal_progress_bar_max)*100);
if(gal_progress_bar_current!=0){
	if($('.meter-out').length==0){
	$('#flscrn').prepend('<div class="meter-out"><div class="meter-text">Processing new photos : 0% Complete</div><div class="meter"><span style="width: 0%"></span></div></div><div class="progress_filler"></div>');
	} else {
	$('.meter > span').css('width', pcent+'%');
	$('.meter-text').html('adding new photos : <b>'+pcent+'%</b> Complete');
	}
var url = $('#new_thumbs > li:first-child').attr('data-url');
$.post( gal_domain+"thumb-gen.php", {id:url, q:gal_quality} ,function(data) {
$('.gal').prepend(data);

$('li[data-url="'+url+'"]').remove();
setTimeout(gal_gen_thumbs, 20);

});
} else {
$('.meter-out, #new_thumbs, .progress_filler').remove();
new flexImages({selector: '.gal', rowHeight: 150});
setTimeout(gal_init_startup(), 200);
}
}


function notify(msg){
$('.notify').prepend('<p>'+msg+'</p>');
$('.notify').show();
}

function gal_infotabs(){

$(".gal-title").each(function(){

var xtarget = $(this).attr('xtarget');
var xval = localStorage.getItem(xtarget);
if(xval === null){ // if null, load from html
var kval = 'gal-expand';
} else {
var kval = xval;
}

if(kval=='gal-collapse'){
$("."+xtarget).hide();
} else {
$("."+xtarget).show();
}

});

}

function gal_json_to_tags(){
var xjson = $.parseJSON($('.gal_data').html());
var xid = $('.gal_data').attr('data-id');
var xhtml = '';
for(var i=0;i<xjson.t;i++){
var str = xjson.data[i].u;
if (str.indexOf("yt[") >= 0){var xtype='yt';} else {var xtype='photo';}
xhtml = xhtml+'<li data-access="'+xjson.data[i].a+'" data-h="'+xjson.h+'" data-xtype="'+xtype+'" data-w="'+xjson.data[i].w+'" class="item gal-type-'+xtype+'"><a href="'+gal_domain+''+gal_quality+'/'+xjson.data[i].u+'"><img class="lazyload" src="'+gal_domain+'css/point.png" xsrc="'+gal_domain+'thumb/'+xjson.data[i].u+'" /></a></li>';
}
$('<ul id="'+xid+'" class="gal flex-images">'+xhtml+'</ul>').insertAfter('[data-id="'+xid+'"]');
lazyload_prepare();
setTimeout(gal_gen_thumbs, 100);
}

function lazyload_start(){
lazyload(0);
lazyload(1);
lazyload(2);
}

function lazyload_prepare(){
jQuery( ".lazyload" ).each(function(i) {
jQuery(this).attr('data-p',i);
});
lazyload_start();
}

function lazyload(xnum){
if(jQuery('[data-p="'+xnum+'"]').length>0){
var isrc = jQuery('[data-p="'+xnum+'"]').attr("xsrc");
jQuery('[data-p="'+xnum+'"]').attr("onload","xlazyload("+xnum+")");
jQuery('[data-p="'+xnum+'"]').attr("src",isrc);
}
}

function xlazyload(xnum){
jQuery('[data-p="'+xnum+'"]').removeClass('lazyload');
jQuery('[data-p="'+xnum+'"]').removeAttr('data-p');
jQuery('[data-p="'+xnum+'"]').removeAttr('onload');
jQuery('[data-p="'+xnum+'"]').removeAttr('style');
var ynum = parseInt(xnum) + 3;
setTimeout("lazyload("+ynum+")", 100);
}

function gal_reset_navigation(){
setTimeout(gal_reset_navigation_now, 10);
}

function gal_reset_navigation_now(){
var cid = parseInt($('.fullscreen-item').attr('data-count'));
var maxid = parseInt($('.fullscreen-item').closest('[data-items]').attr('data-items'));
if(cid==0){
$('.gal-prev').css('visibility','hidden');
} else {
$('.gal-prev').css('visibility','visible');
}

if(cid==(maxid-1)){
$('.gal-next').css('visibility','hidden');
gal_toast('Last photo reached');
} else {
$('.gal-next').css('visibility','visible');
}

$('.gal-counter').html((cid+1)+' / '+maxid);
$('.zoomed-in-text').hide();
$('.gal-counter').show();
clearTimeout(gal_counter_hide_var);
gal_counter_hide_var = setTimeout(gal_counter_hide, 5000);
}

function gal_counter_hide(){
$('.gal-counter').hide();
}


function gal_sharer(){
var xphoto = $('.fullscreen-item > a').attr('href');
var p = xphoto.split('/'+gal_quality+'/');

var shtml = '<ul class="gal-share-menu">\
<li onclick="gal_xtag_toggle()" class="gal-xtag-toggle"><b>Toggle Tags</b></li>\
<li onclick="gal_settings()" class="gal-xtag-gear"><b>Settings</b></li>\
<li onclick="gal_picinfo()" class="gal-share-picinfo"><b>View details</b></li>\
<li onclick="gal_share_media(\''+p[1]+'\')" class="gal-share-social"><b>Share</b></li>\
<li onclick="gal_download_options(\''+p[1]+'\')" class="gal-share-dl"><b>Download</b></li>\
<li onclick="gal_add_to_cart(\''+p[1]+'\')" class="gal-share-cart"><b>Send to Cart</b></li>\
</ul>';

$('.gal-share').html(shtml);
}

function gal_info_toggle(){
$('.gal-prev, .gal-next').toggleClass('gal-mobile-control');
}


function gal_xtag_toggle(){
if($('#xtag-element').hasClass('panzoom-exclude')){
$('#xtag-element').removeClass('panzoom-exclude');
gal_toast('Tagging diabled');
} else {
$('#xtag-element').addClass('panzoom-exclude');
gal_toast('Tagging enabled');
}
gal_mobile_controls('hide');
}

function gal_picinfo(){
jQuery('body').toggleClass('gal-stats-pinned');
setTimeout("gal_ajax_exif()", 1000);
gal_mobile_controls('hide');
if($(window).width() < $(window).height() && $('body').hasClass('gal-stats-pinned')){
gal_toast('Rotate device for best results');
}

// writing to localStorage
if($('body').hasClass('gal-stats-pinned')){
localStorage.setItem('gal_exif', 'yes');
} else {
localStorage.setItem('gal_exif', 'no');
}
$(window).trigger('resize');
}


function gal_add_to_cart(xpic){
var xval = localStorage.getItem('gal_cart_json');

if(xval === null){ // if null
var xobj = {xtot:0, pic:[]};
} else {
var xobj = jQuery.parseJSON(xval);
}

	if(xobj.pic.indexOf(xpic) !== -1){
	gal_toast("Image exists in cart");
	} else {
	xobj.xtot = parseInt(xobj.xtot)+1;
	xobj.pic.push(xpic); 
	var xjson = JSON.stringify(xobj);
	localStorage.setItem('gal_cart_json', xjson);
	jQuery('.gal-cart-indicator').attr('data-items', xobj.xtot);
	jQuery('.gal-cart-indicator > b').html(xobj.xtot);
	gal_toast("Image added to cart");
	}
}

function gal_cart_remove(xpic){

// get and decode from localstorage
var xval = localStorage.getItem('gal_cart_json');
var xobj = jQuery.parseJSON(xval);

// remove from array and update count by -1
xobj.xtot = parseInt(xobj.xtot)-1;
xobj.pic.pop(xpic); 

// encode to json and save to localstorage
var xjson = JSON.stringify(xobj);
localStorage.setItem('gal_cart_json', xjson);

// notify
gal_toast("Image removed from cart");
$('.gal_cart > li[xpic="'+xpic+'"]').remove();
jQuery('.gal-cart-indicator').attr('data-items', xobj.xtot);
jQuery('.gal-cart-indicator > b').html(xobj.xtot);
}


function gal_show_cart(){
var xval = localStorage.getItem('gal_cart_json');
if(xval === null){ // if null
var uhtml = '<li>Your cart is empty!</li>';
} else {
var xjson = jQuery.parseJSON(xval);
var total = parseInt(xjson.xtot);
if(total!=0){
var uhtml = '';

for(i in xjson.pic){
uhtml = uhtml + '<li style="background-image:url('+gal_domain+'thumb/'+xjson.pic[i]+')" xpic="'+xjson.pic[i]+'"><b onclick="gal_cart_remove(\''+xjson.pic[i]+'\')">X</b></li>';
}

} else {
uhtml = '<li>Your cart is empty!</li>';
}

phpl_alert('<ul class="gal_cart">'+uhtml+'</ul>', 'Your items in cart');
}
}


function gal_share_media(xpic){

var xhtml = '<table class="phpl-alert-table gal-link-button_table">';
for(var i=0; i<gal_share_network_keys.length;++i){
xhtml = xhtml + '<tr><td width="60"><b>'+gal_share_network_names[i]+'</b></td><td>\
<a target="_blank" href="'+gal_domain+'phpix-share.php?type='+gal_share_network_keys[i]+'&q=full&pic='+xpic+'">SHARE NOW</a>\
</td></tr>';
}
xhtml = xhtml + '<tr>\
<tr><td><b>Copy link</b></td><td><input type="text" value="'+gal_domain+'u/'+xpic+'" /></td></tr>\
</tr></table>';

phpl_alert(xhtml, 'Share on Social Media');
}


function printExternal(url) {
    var printWindow = window.open( url, 'Print');
    printWindow.addEventListener('load', function(){
        printWindow.print();
        printWindow.close();
    }, true);
}


function gal_toast(xtext){
jQuery('.gal-toast').remove();
jQuery('.fullscreen').append('<div class="gal-toast"><div class="gal-toast-text">'+xtext+'</div></div>');
jQuery(".gal-toast").delay(2000).fadeOut("slow", function(){
jQuery('.gal-toast').remove();
});
}


function gal_drag_scroll(){
var sWidth = $(window).width();
var sHeight = $(window).height();
$('.gal-touch-zoom-ctr').show();
$('.gal-touch-zoom-ctr').css('width', sWidth+'px');
$('.gal-touch-zoom-ctr').css('height', sHeight+'px');

// scale the image
var pWidth = $('.gal-touch-zoom').width();
var pHeight = $('.gal-touch-zoom').height();
$('.gal-touch-zoom').css('height', pHeight+'px');
$('.gal-touch-zoom').css('width', pWidth+'px');
//$('.gal-touch-zoom').dragscroll();
}
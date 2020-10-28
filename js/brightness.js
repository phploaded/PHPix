/*  
create a variable called gal_brightness_elements, then define all the css selectors, where brightness should be applied
example :
var gal_brightness_elements = 'img, .img, canvas';
*/
var gal_vars_brightness = 1;
var gal_brightness_elements = '';

jQuery(document).ready(function(){

var xbright = localStorage.getItem('gal_brightness');
if(xbright !== null){
gal_vars_brightness = xbright;
}

if($('#gal-xtra-css').length==0){
$('body').append('<div id="gal-xtra-css"></div>');
}

gal_set_brightness(gal_vars_brightness);

});


function gal_brightness(){
if(typeof phpl_close_alert == 'function') {
phpl_close_alert(); 
}

if($('.gal-gamma-ctr').length==0){
var xhtml = '<div class="gal-gamma-ctr"><div class="gal-gamma-out">\
<b onclick="gal_set_brightness_val(1)" class="gal-gamma-reset"></b>\
<div class="gal-gamma-slider"><input onchange="gal_set_brightness(this.value)" type="range" id="gal-gamma-range" value="'+gal_vars_brightness+'" name="bright" min="0.5" step="0.05" max="1"></div>\
<b onclick="gal_save_brightness()" class="gal-gamma-apply"></b>\
</div></div>';

if($('#flscrn').length==1){
$('#flscrn').append(xhtml);
} else {
$('body').append(xhtml);
}

} else {
$('.gal-gamma-ctr').remove();
}
}

function gal_set_brightness(xval){
var xhtml = '<style type="text/css">'+gal_brightness_elements+'{opacity:'+xval+';}</style>';
$('#gal-xtra-css').html(xhtml);
}

function gal_save_brightness(){
gal_vars_brightness = $('#gal-gamma-range').val();
localStorage.setItem('gal_brightness', gal_vars_brightness);
$('.gal-gamma-ctr').remove();
}

function gal_set_brightness_val(xval){
$('#gal-gamma-range').val(xval);
$('#gal-gamma-range').trigger('change');
}
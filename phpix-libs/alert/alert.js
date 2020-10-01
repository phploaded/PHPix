function phpl_alert(data, title, footer,xid){
if(title===undefined){title='Message from server';}
if(footer===undefined || footer==''){footer='<div class="phpl-alert-btn-danger phpl-alert-close">Close</div>';}
if(xid===undefined){
var xtot = $('.phpl-alert-ctr').length+1;
} else {
var xtot = xid;
}
xhtml = '<div id="phpl-alert-'+xtot+'" style="display:none;" class="phpl-alert-ctr">\
<div class="phpl-alert-bg"></div>\
<div class="phpl-alert-box-outer">\
<div class="phpl-alert-box-inner">\
<div class="phpl-alert-box">\
<div class="phpl-alert-box-top">\
<div class="phpl-alert-box-top-text">'+title+'</div>\
<div class="phpl-alert-box-top-close" title="Close">X</div>\
<div style="clear:both;"></div>\
</div>\
<div class="phpl-alert-box-middle">'+data+'</div>\
<div class="phpl-alert-box-bottom">'+footer+'\
<div style="clear:both;"></div>\
</div></div></div></div></div>';
if($('#flscrn').length==1){
$('#flscrn').prepend(xhtml);
} else {
$('body').prepend(xhtml);
}
$('.gal-bg').addClass('blurred');
// $('body, html').addClass('no-scroll');
$('#phpl-alert-'+xtot).fadeIn("slow");
}

function phpl_confirm(data, on_click, xid){
if(on_click===undefined){on_click='';} else {on_click=' onclick="'+on_click+'" ';}
footer = '<div class="phpl-alert-btn-danger phpl-alert-close">Cancel</div> <div class="phpl-alert-btn-success phpl-alert-close" '+on_click+'>Continue</div>';
phpl_alert(data,'Please confirm to continue', footer, xid);
}

function phpl_close_alert(xid){
if(xid===undefined || xid===''){var xid='phpl-alert-1';}
$('#'+xid).fadeOut("slow", function(){
$('#'+xid).remove();
$('.gal-bg').removeClass('blurred');
// $('.no-scroll').removeClass('no-scroll');
});
}


jQuery(document).ready(function(){
$('body').on('click', '.phpl-alert-close, .phpl-alert-box-top-close', function(){
var xid = $(this).closest('.phpl-alert-ctr').attr('id');
phpl_close_alert(xid);
});
});
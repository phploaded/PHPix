function xtag_init(){
var xthis = $('#gal-reel-now img');
var w = $(xthis).width();
var h = $(xthis).height();
$(xthis).addClass('xtag-img');
$(xthis).wrap(function() {
return '<div id="xtag-element" style="width:'+w+'px;height:'+h+'px;"></div>';
});


$('body').on('click', '#xtag-element', function(e) {
if($('#xtag-element').hasClass('panzoom-exclude')){
if($(e.target).hasClass('xtag-img')){

var x = e.offsetX;
var y = e.offsetY;

var w = $(this).width();
var h = $(this).height();

var xpos = 'right';
var ypos = 'top';

if(x<25){x=25;}
if(x>(w-50)){x=w-25;}
if(y<25){y=25;}
if(y>(h-50)){y=h-25;}

if(x>(w/2)){ xpos = 'left';}
if(y>(h/2)){ ypos = 'bottom'; }

// convert to percent
var py = y/h*100;
var px = x/w*100;

$('.xtag-ctr').remove();
$(this).append('<div style="top:'+py+'%;left:'+px+'%;" class="xtag-ctr xtag-pos-'+xpos+'-'+ypos+'"><div class="xtag-inner">\
<div class="xtag-focus"></div>\
<div class="xtag-form">\
<textarea class="xtag-input"></textarea>\
<input class="xtag-save" type="button" value="save" onclick="xtag_save()">\
<input class="xtag-close" type="button" value="close" onclick="xtag_close()">\
</div>\
</div></div>');

$('.xtag-input').trigger('focus');

}
}
});

var xurl = $('#gal-reel-now img').attr('src');
xtag_loadCacheTags(xurl);
xtag_load_DBtags(xurl);
}


jQuery(document).ready(function(){



});


function xtag_getFilenameFromUrl(url) {
var index = url.lastIndexOf('/');
url = url.substring(index + 1);
var xurl = url.split('.');
return xurl[0];
}



function xtag_loadCacheTags(xurl){
var xpic = xtag_getFilenameFromUrl(xurl);
var xval = localStorage.getItem('xtag_'+xpic);

	if(xval !== null){
		var xjson = jQuery.parseJSON(xval);
		var total = parseInt(xjson.xtot);
		if(total!=0){
			for(i in xjson.tag){ // xjson.pic[i]
			xtag_apply_tag(xjson.tagdata[i], xjson.tag[i]);
			}
		}
	}

}


function xtag_load_DBtags(xurl){
var xpic = xtag_getFilenameFromUrl(xurl);
var xval = localStorage.getItem('xtag_db_'+xpic);
if(xval === null){

$.get( gal_domain+"phpix-ajax.php?method=read&id="+xpic, function( data ) {
var x = data.substring(0, data.lastIndexOf(','));
var xx = '['+x+']';
var obj = $.parseJSON(xx);
	for(i=0;i<obj.length;++i){
		for(key in obj[i]){ 
		xtag_apply_tag(obj[i][key], key, 'obj', 'dbtag');
		}
	}
});
}

}



function xtag_save(){
var xpic = xtag_getFilenameFromUrl($('.xtag-img').attr('src'));
var xcss = $('.xtag-ctr').attr('style');
var xtext = $('.xtag-input').val();
var xclas2 = ($('.xtag-ctr').attr('class')).replace('xtag-ctr', '');
var xclas = xclas2.replace(' ', '');


if(xtag_save_db===true){
	
	$.post( gal_domain+"phpix-ajax.php?method=save", {pic:xpic, css:xcss, txt:xtext, clas:xclas}, function( data ) {
	var xobj = $.parseJSON(data);
	var xkey = Object.keys(xobj)[0];
	//console.log(Object.keys(xobj));
	xtag_apply_tag(xobj[xkey], xkey, 'obj', 'dbtag');
	$('.xtag-ctr').remove();
	});
	
} else {
var xobj = {pic:xpic, css:xcss, txt:xtext, clas:xclas};
var xdata = JSON.stringify(xobj);
var xtag_id = uniqid();
xtag_apply_tag(xdata, xtag_id);
$('.xtag-ctr').remove();
xtag_saveToCache(xpic, xdata, xtag_id);
}
}

function uniqid(a = "", b = false) {
    const c = Date.now()/1000;
    let d = c.toString(16).split(".").join("");
    while(d.length < 14) d += "0";
    let e = "";
    if(b){
        e = ".";
        e += Math.round(Math.random()*100000000);
    }
    return a + d + e;
}

function xtag_saveToCache(xpic, xdata, xtag_id){
var xval = localStorage.getItem('xtag_'+xpic);
if(xval === null){ // if null
var xobj = {xtot:0, tag:[], tagdata:[]};
} else {
var xobj = jQuery.parseJSON(xval);
}

	xobj.xtot = parseInt(xobj.xtot)+1;
	xobj.tag.push(xtag_id); 
	xobj.tagdata.push(xdata);
	var xjson = JSON.stringify(xobj);
	localStorage.setItem('xtag_'+xpic, xjson);

}

/* data, id, dataFormat, CachedOrDB */
function xtag_apply_tag(xdata, xtag_id, xtype = 'json', xtag_type = 'cachetag'){
if(xtype=='json'){
var json = $.parseJSON(xdata);
} else {
var json = xdata;
}

if(xtag_type=='cachetag' || gal_vars_uid!=0){
var xdel = '<div onclick="xtag_delete_tag(this)" class="xtag-quit">X</div>';
} else {
var xdel = '';
}

$('#xtag-element').append('<div id="xtag-item-'+xtag_id+'" style="'+json.css+'" class="xtag-tag xtag-'+xtag_type+' panzoom-exclude '+json.clas+'"><div class="xtag-area"></div><div class="xtag-text">'+xdel+''+json.txt+'</div></div>');
}


function xtag_delete_tag(xthis){
var xid = $(xthis).closest('.xtag-tag').attr('id');
var xpic = xtag_getFilenameFromUrl($(xthis).closest('#xtag-element').find('.xtag-img').attr('src'));

if($('#'+xid).hasClass('xtag-dbtag')){
var tid = xid.replace('xtag-item-', '');
$.get( gal_domain+"phpix-ajax.php?method=delete&id="+xpic+"&tid="+tid, function( data ) {
$('#'+xid).remove();
});

} else {
// get and decode from localstorage
var xval = localStorage.getItem('xtag_'+xpic);
var xobj = jQuery.parseJSON(xval);

// remove from array and update count by -1
xobj.xtot = parseInt(xobj.xtot)-1;
var narr = xobj.tag;
var index = xobj.tag.indexOf(xid.replace('xtag-item-', ''));
xobj.tag.splice(index, 1);
xobj.tagdata.splice(index, 1);

// encode to json and save to localstorage
var xjson = JSON.stringify(xobj);
localStorage.setItem('xtag_'+xpic, xjson);

// notify
$('#'+xid).remove();
}

}


function xtag_close(){
$('.xtag-ctr').remove();
}
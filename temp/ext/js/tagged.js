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
var spotdata = '';
for (let i=0; i<spotsArray.length; i++) {
spotdata = spotdata + '<option value="'+spotsArray[i]+'"></option>';
}

$(this).append('<div style="top:'+py+'%;left:'+px+'%;" class="xtag-ctr xtag-pos-'+xpos+'-'+ypos+'"><div class="xtag-inner">\
<div class="xtag-focus"></div>\
<div class="xtag-form">\
<datalist id="xtag-people-list" class="xtag-select">'+spotdata+'</datalist>\
<input list="xtag-people-list" type="text" placeholder="Type something" class="xtag-input">\
<input class="xtag-save" type="button" value="save" onclick="xtag_save()">\
<input class="xtag-close" type="button" value="close" onclick="xtag_close()">\
<input class="xtag-delete" type="button" value="delete" onclick="xtag_delete_template()">\
</div>\
</div></div>');

$('.xtag-input').trigger('focus');

}
}
});

var xurl = $('#gal-reel-now img').attr('src');
xtag_loadCacheTags(xurl);
xtag_load_DBtags(xurl);
setTimeout(function() {
	gal_xtag_auto();
	}, 1000);
}


jQuery(document).ready(function(){

    // Event listener for detecting selection
    $('body').on('input', '.xtag-input', function () {
        var value = $(this).val();
        var isMatch = $('#xtag-people-list option').filter(function () {
            return $(this).val() === value;
        }).length > 0;

        if (isMatch) {
            $('.xtag-delete').show(); // Show the delete button if a match is found
        } else {
            $('.xtag-delete').hide(); // Hide the delete button if no match
        }
    });

});

function xtag_deleteSpotOption(value) {
    // Get all options from the datalist
    var isFound = $('#xtag-people-list option').filter(function() {
        return $(this).val() === value; // Check if option value matches input value
    }).length > 0;

    // Enable or hide the .xtag-delete button based on whether the value is found
    if (isFound) {
        $('.xtag-delete').show(); // Show or enable the button
    } else {
        $('.xtag-delete').hide(); // Hide or disable the button
    }
}

function xtag_delete_template() {
    // Get the value of the input field
    var xval = $('.xtag-input').val();

    // Display confirmation modal
    phpl_confirm('Are you sure you want to delete this template tag?', function() {
        // If confirmed, proceed with the deletion
        $.get(gal_domain + "phpix-ajax.php?method=delete_template_tag&title=" + encodeURIComponent(xval), function(data) {
            // Remove the <option> from the datalist
            $('.xtag-select option').filter(function() {
                return $(this).val() === xval;
            }).remove();

            // Remove the value from spotsArray
            const index = spotsArray.indexOf(xval);
            if (index > -1) {
                spotsArray.splice(index, 1); // Remove the element
            }

            // Show a toast message
            gal_toast('Template tag deleted.');
        });

        // Clear the input field
        $('.xtag-input').val('');
    });
}



function xtag_getFilenameFromUrl(url) {
    if (!url || typeof url !== 'string') {
        console.error('Invalid URL:', url);
        return ''; // Return an empty string or a default value
    }

    const index = url.lastIndexOf('/');
    const filename = (index !== -1) ? url.substring(index + 1) : url;

    const xurl = filename.split('.');
    return xurl[0] || ''; // Return the first part or an empty string
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
var xselect = $('.xtag-input').val();
var xclas2 = ($('.xtag-ctr').attr('class')).replace('xtag-ctr', '');
var xclas = xclas2.replace(' ', '');


if(xtag_save_db===true){

	$.post( gal_domain+"phpix-ajax.php?method=save", {pic:xpic, css:xcss, txt:xtext, clas:xclas, sel:xselect}, function( data ) {
	var xobj = $.parseJSON(data);
	var xkey = Object.keys(xobj)[0];
	
    const $datalist = $("#xtag-people-list");

    // Check if the value already exists in the datalist
    const exists = $datalist.find("option").filter(function () {
        return $(this).val().toLowerCase() === xtext.toLowerCase();
    }).length > 0;

    if (!exists) {
        // Add the new option to the datalist
        $datalist.append($("<option>").val(xtext));

        // Push the new element to the array
        spotsArray.push(xtext);
        spotsArray.sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }));
    }
	
	//console.log(Object.keys(xobj));
	xtag_apply_tag(xobj[xkey], xkey, 'obj', 'dbtag');
	$('.xtag-ctr').remove();
	gal_toast('Tag saved to account.');
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
	gal_toast('Tag saved to browser cache.');

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
var textOnly = $('#'+xid+' .xtag-text').contents().filter(function() {
    return this.nodeType === Node.TEXT_NODE; // Filters text nodes
}).text().trim(); 

phpl_confirm(
    'Are you sure to delete <b>' + textOnly + '</b> spot?<br>',
    function () { 
        xtag_tag_deleted(xid); 
    },
    'xtag-' + xid // Pass the unique xid here
);


}


function xtag_tag_deleted(xid){
var xpic = xtag_getFilenameFromUrl($('#'+xid).closest('#xtag-element').find('.xtag-img').attr('src'));

if($('#'+xid).hasClass('xtag-dbtag')){
var tid = xid.replace('xtag-item-', '');
$.get( gal_domain+"phpix-ajax.php?method=delete&id="+xpic+"&tid="+tid, function( data ) {
$('#'+xid).remove();
gal_toast('Tag removed from account.');
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
gal_toast('Tag removed from browser cache.');
}

}


function xtag_close(){
$('.xtag-ctr').remove();
}
var XTAG_MIN_BOX_SIZE = 48;
var XTAG_DEFAULT_BOX_MIN = 72;
var XTAG_DEFAULT_BOX_MAX = 180;
var XTAG_POSITION_CLASSES = 'xtag-pos-left-top xtag-pos-left-bottom xtag-pos-right-top xtag-pos-right-bottom';
var xtag_editor_state = null;

function xtag_init() {
var $image = $('#gal-reel-now img');
if ($image.length === 0) {
return;
}

var w = $image.width();
var h = $image.height();
$image.addClass('xtag-img');

if ($image.parent('#xtag-element').length === 0) {
$image.wrap(function() {
return '<div id="xtag-element" style="width:' + w + 'px;height:' + h + 'px;"></div>';
});
} else {
$image.parent('#xtag-element').css({ width: w + 'px', height: h + 'px' });
}

xtag_bindEvents();

var xurl = $('#gal-reel-now img').attr('src');
xtag_loadCacheTags(xurl);
xtag_load_DBtags(xurl);
setTimeout(function() {
gal_xtag_auto();
}, 1000);
}

function xtag_bindEvents() {
if (xtag_bindEvents.bound === true) {
return;
}
xtag_bindEvents.bound = true;

$('body').on('click.xtagCreate', '#xtag-element', function(e) {
if (!$('#xtag-element').hasClass('panzoom-exclude')) {
return;
}

if (!$(e.target).hasClass('xtag-img')) {
return;
}

var point = xtag_getLocalPoint(this, e);
xtag_openEditor(point.x, point.y);
});

$('body').on('input.xtagInput', '.xtag-input', function() {
var value = $(this).val();
var isMatch = $('#xtag-people-list option').filter(function() {
return $(this).val() === value;
}).length > 0;

if (isMatch) {
$('.xtag-delete').show();
} else {
$('.xtag-delete').hide();
}
});

$('body').on('pointerdown.xtagEditor', '.xtag-ctr .xtag-focus, .xtag-ctr .xtag-handle', function(e) {
if (!$('#xtag-element').hasClass('panzoom-exclude')) {
return;
}

var $editor = $(this).closest('.xtag-ctr');
if ($editor.length === 0) {
return;
}

e.preventDefault();
e.stopPropagation();

xtag_editor_state = {
pointerId: e.pointerId,
mode: $(this).hasClass('xtag-handle') ? 'resize' : 'move',
handle: $(this).data('handle') || '',
startPoint: xtag_getEventPoint(e),
startBox: xtag_getEditorBox($editor),
container: xtag_getContainerBox()
};
});

$(document).on('pointermove.xtagEditor', function(e) {
if (!xtag_editor_state || xtag_editor_state.pointerId !== e.pointerId) {
return;
}

var $editor = $('.xtag-ctr');
if ($editor.length === 0) {
xtag_editor_state = null;
return;
}

e.preventDefault();

var point = xtag_getEventPoint(e);
var dx = point.x - xtag_editor_state.startPoint.x;
var dy = point.y - xtag_editor_state.startPoint.y;
var nextBox;

if (xtag_editor_state.mode === 'move') {
nextBox = {
left: xtag_editor_state.startBox.left + dx,
top: xtag_editor_state.startBox.top + dy,
width: xtag_editor_state.startBox.width,
height: xtag_editor_state.startBox.height
};
nextBox = xtag_constrainBox(nextBox, xtag_editor_state.container.width, xtag_editor_state.container.height);
} else {
nextBox = xtag_resizeBox(
xtag_editor_state.startBox,
xtag_editor_state.handle,
dx,
dy,
xtag_editor_state.container.width,
xtag_editor_state.container.height
);
}

xtag_applyEditorBox($editor, nextBox);
});

$(document).on('pointerup.xtagEditor pointercancel.xtagEditor', function(e) {
if (!xtag_editor_state || xtag_editor_state.pointerId !== e.pointerId) {
return;
}

xtag_editor_state = null;
});
}

function xtag_getEventPoint(event) {
var source = event.originalEvent || event;
if (source.changedTouches && source.changedTouches.length > 0) {
source = source.changedTouches[0];
} else if (source.touches && source.touches.length > 0) {
source = source.touches[0];
}

return {
x: Number(source.clientX || 0),
y: Number(source.clientY || 0)
};
}

function xtag_getLocalPoint(element, event) {
var rect = element.getBoundingClientRect();
var point = xtag_getEventPoint(event);
return {
x: point.x - rect.left,
y: point.y - rect.top
};
}

function xtag_getContainerBox() {
var $element = $('#xtag-element');
return {
width: $element.width(),
height: $element.height()
};
}

function xtag_clamp(value, min, max) {
if (max < min) {
return min;
}
return Math.max(min, Math.min(max, value));
}

function xtag_formatPercent(value) {
var fixed = Number(value).toFixed(12);
fixed = fixed.replace(/0+$/, '').replace(/\.$/, '');
return fixed === '' ? '0' : fixed;
}

function xtag_styleHasBoxDimensions(styleText) {
if (!styleText) {
return false;
}
return /(?:^|;)\s*width\s*:/i.test(styleText) && /(?:^|;)\s*height\s*:/i.test(styleText);
}

function xtag_getSpotOptionsHtml() {
var spotdata = '';
for (var i = 0; i < spotsArray.length; i++) {
spotdata += '<option value="' + spotsArray[i] + '"></option>';
}
return spotdata;
}

function xtag_getDefaultBox(x, y, width, height) {
var size = Math.min(Math.min(width, height) * 0.22, XTAG_DEFAULT_BOX_MAX);
size = Math.max(size, XTAG_DEFAULT_BOX_MIN);
return xtag_constrainBox({
left: x - (size / 2),
top: y - (size / 2),
width: size,
height: size
}, width, height);
}

function xtag_constrainBox(box, maxWidth, maxHeight) {
var width = xtag_clamp(Number(box.width || 0), XTAG_MIN_BOX_SIZE, maxWidth);
var height = xtag_clamp(Number(box.height || 0), XTAG_MIN_BOX_SIZE, maxHeight);
var left = xtag_clamp(Number(box.left || 0), 0, Math.max(0, maxWidth - width));
var top = xtag_clamp(Number(box.top || 0), 0, Math.max(0, maxHeight - height));

return {
left: left,
top: top,
width: width,
height: height
};
}

function xtag_resizeBox(startBox, handle, dx, dy, maxWidth, maxHeight) {
var left = startBox.left;
var top = startBox.top;
var right = startBox.left + startBox.width;
var bottom = startBox.top + startBox.height;

if (handle.indexOf('w') !== -1) {
left += dx;
left = Math.min(left, right - XTAG_MIN_BOX_SIZE);
left = Math.max(0, left);
}

if (handle.indexOf('e') !== -1) {
right += dx;
right = Math.max(right, left + XTAG_MIN_BOX_SIZE);
right = Math.min(maxWidth, right);
}

if (handle.indexOf('n') !== -1) {
top += dy;
top = Math.min(top, bottom - XTAG_MIN_BOX_SIZE);
top = Math.max(0, top);
}

if (handle.indexOf('s') !== -1) {
bottom += dy;
bottom = Math.max(bottom, top + XTAG_MIN_BOX_SIZE);
bottom = Math.min(maxHeight, bottom);
}

return xtag_constrainBox({
left: left,
top: top,
width: right - left,
height: bottom - top
}, maxWidth, maxHeight);
}

function xtag_detectPosition(box, width, height) {
var xCenter = box.left + (box.width / 2);
var yCenter = box.top + (box.height / 2);
var xpos = xCenter > (width / 2) ? 'left' : 'right';
var ypos = yCenter > (height / 2) ? 'bottom' : 'top';
return 'xtag-pos-' + xpos + '-' + ypos;
}

function xtag_boxToStyle(box, width, height) {
return 'top:' + xtag_formatPercent((box.top / height) * 100) +
'%;left:' + xtag_formatPercent((box.left / width) * 100) +
'%;width:' + xtag_formatPercent((box.width / width) * 100) +
'%;height:' + xtag_formatPercent((box.height / height) * 100) + '%;';
}

function xtag_getEditorBox($editor) {
return {
left: parseFloat($editor[0].style.left) || 0,
top: parseFloat($editor[0].style.top) || 0,
width: parseFloat($editor[0].style.width) || XTAG_DEFAULT_BOX_MIN,
height: parseFloat($editor[0].style.height) || XTAG_DEFAULT_BOX_MIN
};
}

function xtag_applyEditorBox($editor, box) {
var container = xtag_getContainerBox();
var normalized = xtag_constrainBox(box, container.width, container.height);
var positionClass = xtag_detectPosition(normalized, container.width, container.height);

$editor
.removeClass(XTAG_POSITION_CLASSES)
.addClass('xtag-box ' + positionClass)
.css({
top: normalized.top + 'px',
left: normalized.left + 'px',
width: normalized.width + 'px',
height: normalized.height + 'px'
});
}

function xtag_openEditor(x, y) {
var container = xtag_getContainerBox();
if (container.width <= 0 || container.height <= 0) {
return;
}

xtag_close();

var defaultBox = xtag_getDefaultBox(x, y, container.width, container.height);
var html = '<div class="xtag-ctr xtag-box">' +
'<div class="xtag-inner">' +
'<div class="xtag-focus"></div>' +
'<div class="xtag-handle xtag-handle-n" data-handle="n"></div>' +
'<div class="xtag-handle xtag-handle-e" data-handle="e"></div>' +
'<div class="xtag-handle xtag-handle-s" data-handle="s"></div>' +
'<div class="xtag-handle xtag-handle-w" data-handle="w"></div>' +
'<div class="xtag-handle xtag-handle-nw" data-handle="nw"></div>' +
'<div class="xtag-handle xtag-handle-ne" data-handle="ne"></div>' +
'<div class="xtag-handle xtag-handle-sw" data-handle="sw"></div>' +
'<div class="xtag-handle xtag-handle-se" data-handle="se"></div>' +
'<div class="xtag-form">' +
'<datalist id="xtag-people-list" class="xtag-select">' + xtag_getSpotOptionsHtml() + '</datalist>' +
'<input list="xtag-people-list" type="text" placeholder="Type something" class="xtag-input">' +
'<input class="xtag-save" type="button" value="save" onclick="xtag_save()">' +
'<input class="xtag-close" type="button" value="close" onclick="xtag_close()">' +
'<input class="xtag-delete" type="button" value="delete" onclick="xtag_delete_template()">' +
'</div>' +
'</div>' +
'</div>';

$('#xtag-element').append(html);
xtag_applyEditorBox($('.xtag-ctr'), defaultBox);
$('.xtag-input').trigger('focus');
}

function xtag_normalizeSavedClass(className, cssText) {
var classes = $.trim(className || '').replace(/\s+/g, ' ');
var hasBox = xtag_styleHasBoxDimensions(cssText);

if (hasBox) {
classes = classes.replace(/\bxtag-point\b/g, '').trim();
if (classes.indexOf('xtag-box') === -1) {
classes = ('xtag-box ' + classes).trim();
}
} else {
classes = classes.replace(/\bxtag-box\b/g, '').trim();
if (classes.indexOf('xtag-point') === -1) {
classes = ('xtag-point ' + classes).trim();
}
}

return classes;
}

function xtag_deleteSpotOption(value) {
var isFound = $('#xtag-people-list option').filter(function() {
return $(this).val() === value;
}).length > 0;

if (isFound) {
$('.xtag-delete').show();
} else {
$('.xtag-delete').hide();
}
}

function xtag_delete_template() {
var xval = $('.xtag-input').val();

phpl_confirm('Are you sure you want to delete this template tag?', function() {
$.get(gal_domain + "phpix-ajax.php?method=delete_template_tag&title=" + encodeURIComponent(xval), function() {
$('.xtag-select option').filter(function() {
return $(this).val() === xval;
}).remove();

var index = spotsArray.indexOf(xval);
if (index > -1) {
spotsArray.splice(index, 1);
}

gal_toast('Template tag deleted.');
});

$('.xtag-input').val('');
});
}

function xtag_getFilenameFromUrl(url) {
if (!url || typeof url !== 'string') {
console.error('Invalid URL:', url);
return '';
}

var index = url.lastIndexOf('/');
var filename = index !== -1 ? url.substring(index + 1) : url;
var xurl = filename.split('.');
return xurl[0] || '';
}

function xtag_loadCacheTags(xurl) {
var xpic = xtag_getFilenameFromUrl(xurl);
var xval = localStorage.getItem('xtag_' + xpic);

if (xval !== null) {
var xjson = jQuery.parseJSON(xval);
var total = parseInt(xjson.xtot, 10);
if (total !== 0) {
for (var i in xjson.tag) {
xtag_apply_tag(xjson.tagdata[i], xjson.tag[i]);
}
}
}
}

function xtag_load_DBtags(xurl) {
var xpic = xtag_getFilenameFromUrl(xurl);
var xval = localStorage.getItem('xtag_db_' + xpic);
if (xval === null) {
$.get(gal_domain + "phpix-ajax.php?method=read&id=" + xpic, function(data) {
var x = data.substring(0, data.lastIndexOf(','));
var xx = '[' + x + ']';
var obj = $.parseJSON(xx);
for (var i = 0; i < obj.length; ++i) {
for (var key in obj[i]) {
xtag_apply_tag(obj[i][key], key, 'obj', 'dbtag');
}
}
});
}
}

function xtag_save() {
var xpic = xtag_getFilenameFromUrl($('.xtag-img').attr('src'));
var $editor = $('.xtag-ctr');
if ($editor.length === 0) {
return;
}

var container = xtag_getContainerBox();
var editorBox = xtag_getEditorBox($editor);
var normalizedBox = xtag_constrainBox(editorBox, container.width, container.height);
xtag_applyEditorBox($editor, normalizedBox);

var xcss = xtag_boxToStyle(normalizedBox, container.width, container.height);
var xtext = $('.xtag-input').val();
var xselect = $('.xtag-input').val();
var xclas = xtag_normalizeSavedClass(($editor.attr('class') || '').replace('xtag-ctr', ''), xcss);

if (xtag_save_db === true) {
$.post(gal_domain + "phpix-ajax.php?method=save", { pic: xpic, css: xcss, txt: xtext, clas: xclas, sel: xselect }, function(data) {
var xobj = $.parseJSON(data);
var xkey = Object.keys(xobj)[0];

var $datalist = $("#xtag-people-list");
var exists = $datalist.find("option").filter(function() {
return $(this).val().toLowerCase() === xtext.toLowerCase();
}).length > 0;

if (!exists) {
$datalist.append($("<option>").val(xtext));
spotsArray.push(xtext);
spotsArray.sort(function(a, b) {
return a.localeCompare(b, undefined, { sensitivity: 'base' });
});
}

xtag_apply_tag(xobj[xkey], xkey, 'obj', 'dbtag');
xtag_close();
gal_toast('Tag saved to account.');
});
} else {
var xobj = { pic: xpic, css: xcss, txt: xtext, clas: xclas };
var xdata = JSON.stringify(xobj);
var xtag_id = uniqid();
xtag_apply_tag(xdata, xtag_id);
xtag_close();
xtag_saveToCache(xpic, xdata, xtag_id);
}
}

function uniqid(a = "", b = false) {
var c = Date.now() / 1000;
var d = c.toString(16).split(".").join("");
while (d.length < 14) {
d += "0";
}
var e = "";
if (b) {
e = ".";
e += Math.round(Math.random() * 100000000);
}
return a + d + e;
}

function xtag_saveToCache(xpic, xdata, xtag_id) {
var xval = localStorage.getItem('xtag_' + xpic);
var xobj;
if (xval === null) {
xobj = { xtot: 0, tag: [], tagdata: [] };
} else {
xobj = jQuery.parseJSON(xval);
}

xobj.xtot = parseInt(xobj.xtot, 10) + 1;
xobj.tag.push(xtag_id);
xobj.tagdata.push(xdata);
var xjson = JSON.stringify(xobj);
localStorage.setItem('xtag_' + xpic, xjson);
gal_toast('Tag saved to browser cache.');
}

function xtag_apply_tag(xdata, xtag_id, xtype = 'json', xtag_type = 'cachetag') {
var json = xtype === 'json' ? $.parseJSON(xdata) : xdata;
var xdel = '';
if (xtag_type === 'cachetag' || gal_vars_uid != 0) {
xdel = '<div onclick="xtag_delete_tag(this)" class="xtag-quit">X</div>';
}

var normalizedClass = xtag_normalizeSavedClass(json.clas, json.css);
$('#xtag-element').append('<div id="xtag-item-' + xtag_id + '" style="' + json.css + '" class="xtag-tag xtag-' + xtag_type + ' panzoom-exclude ' + normalizedClass + '"><div class="xtag-area"></div><div class="xtag-text">' + xdel + '' + json.txt + '</div></div>');
}

function xtag_delete_tag(xthis) {
var xid = $(xthis).closest('.xtag-tag').attr('id');
var textOnly = $('#' + xid + ' .xtag-text').contents().filter(function() {
return this.nodeType === Node.TEXT_NODE;
}).text().trim();

phpl_confirm(
'Are you sure to delete <b>' + textOnly + '</b> spot?<br>',
function() {
xtag_tag_deleted(xid);
},
'xtag-' + xid
);
}

function xtag_tag_deleted(xid) {
var xpic = xtag_getFilenameFromUrl($('#' + xid).closest('#xtag-element').find('.xtag-img').attr('src'));

if ($('#' + xid).hasClass('xtag-dbtag')) {
var tid = xid.replace('xtag-item-', '');
$.get(gal_domain + "phpix-ajax.php?method=delete&id=" + xpic + "&tid=" + tid, function() {
$('#' + xid).remove();
gal_toast('Tag removed from account.');
});
} else {
var xval = localStorage.getItem('xtag_' + xpic);
var xobj = jQuery.parseJSON(xval);

xobj.xtot = parseInt(xobj.xtot, 10) - 1;
var index = xobj.tag.indexOf(xid.replace('xtag-item-', ''));
xobj.tag.splice(index, 1);
xobj.tagdata.splice(index, 1);

var xjson = JSON.stringify(xobj);
localStorage.setItem('xtag_' + xpic, xjson);

$('#' + xid).remove();
gal_toast('Tag removed from browser cache.');
}
}

function xtag_close() {
$('.xtag-ctr').remove();
xtag_editor_state = null;
}

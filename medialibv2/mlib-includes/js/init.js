/* Path to the folder where mlib-config.php exists */
mlib_domain = 'http://shopfblikes.com/medialibv2/';


/* Load jQuery if not already loaded */
if(!window.jQuery){
var script = document.createElement('script');
script.type = "text/javascript";
script.src = mlib_domain+'mlib-includes/js/jquery-1.11.1.min.js';
document.getElementsByTagName('head')[0].appendChild(script);
}


/* Include required JS/CSS Files asynchronously to improve server response time of web-pages and reduce hassle */
var mlib_includes = '<link href="'+mlib_domain+'mlib-includes/css/mlib.css" rel="stylesheet" type="text/css" />\
<link href="'+mlib_domain+'mlib-includes/dropzone/css/basic.css" rel="stylesheet" type="text/css" />\
<link href="'+mlib_domain+'mlib-includes/dropzone/css/dropzone.min.css" rel="stylesheet" type="text/css" />\
<script src="'+mlib_domain+'mlib-includes/dropzone/dropzone.min.js" type="text/javascript"></script>\
<script src="'+mlib_domain+'mlib-includes/js/mlib.js" type="text/javascript"></script>';
 // document.write(mlib_includes);
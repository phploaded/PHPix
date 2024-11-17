<form name="fdfdfd" action="" method="post">
<div class="page-header" id="banner">
<h2><i class="fa fa-gear"></i> Display Options <button class="pull-right btn btn-warning btn-medium">Save changes</button></h2>
</div>

<div class="form-horizontal">
<?php 

if(!isset($_POST['ui-setting-animation'])){$_POST['ui-setting-animation']='';} 

if($_POST['ui-setting-animation']!=''){

$html = "
var gal_vars_tags = '".$_POST['ui-photo-tags']."';
var gal_vars_mini_thumbs = '".$_POST['ui-thumbs']."';
var gal_vars_bgmode = '".$_POST['ui-setting-bgmode']."'; 

var gal_vars_themes = ".json_encode($_POST['themes']).";
var gal_vars_theme = '".$_POST['ui-setting-theme']."';

var gal_slide_animations = ".json_encode($_POST['animations']).";
var gal_slide_animation = '".$_POST['ui-setting-animation']."'; 

var gal_vars_button_set = '".$_POST['ui-setting-buttons']."';
var gal_vars_button_sets = ".json_encode($_POST['buttons']).";
";

$file = 'js/main-config.js';
@unlink($file);
$fp = fopen($file, 'w');
fwrite($fp, $html);
fclose($fp);


$html = "<?php 

\$default_admin_theme = '".$_POST['admin-theme']."';
\$default_admin_menu = '".$_POST['admin-theme-menu']."';

\$mlib_upload_resolution = '".$_POST['admin-resize-photo']."';

 ?>";
$file = 'phpix-user-config.php';
@unlink($file);
$fp = fopen($file, 'w');
fwrite($fp, $html);
fclose($fp);

echo'<script>document.location.href = \''.$domain.'phpix-manage.php?page=options\';</script>';

}

$swipe = dir_to_html('phpix-imports/animations', 'css');
$theme = dir_to_html('phpix-imports/themes'); 
$button = dir_to_html('phpix-imports/buttons'); 
$admin_theme = dir_to_html('phpix-imports/adminui', 'css'); 

 ?>
<div class="form-group">
<label for="admin-theme" class="col-md-3 control-label">Resize before upload</label>
<div class="col-md-9">
<select id="admin-resize-photo" name="admin-resize-photo" class="form-control">
<option value="">Upload without resizing</option>
<option value="4k">4K UHD MAX (3840 pixels on wide side)</option>
<option value="8k">8K UHD MAX (7680 pixels on wide side)</option>
<option value="16k">16K UHD MAX (7680 pixels on wide side)</option>
</select>
<br /><p>Photos can be resized, even before they get uploaded. The browser used by uploader will do the processing, so it might feel sluggish before any file is ready for upload. However, this saves bandwidth for uploading by admin, bandwidth for download by users and disk space on server due to low file size. It will make a huge difference in long run.</p>
</div>
</div>

<div class="form-group">
<label for="admin-theme" class="col-md-3 control-label">Admin area theme</label>
<div class="col-md-9">
<select id="admin-theme" onchange="admin_preview_theme()" name="admin-theme" class="form-control">
<?php echo $admin_theme['options']; ?>
</select>
</div>
</div>

<div class="form-group">
<label for="admin-theme-menu" class="col-md-3 control-label">Admin area menu style</label>
<div class="col-md-9">
<select id="admin-theme-menu" onchange="admin_preview_theme()" name="admin-theme-menu" class="form-control">
<option>default</option>
<option>inverse</option>
</select>
</div>
</div>


<div class="form-group">
<label for="inputEmail" class="col-md-3 control-label">Default Swipe animation</label>
<div class="col-md-9">
<select id="ui-setting-animation" name="ui-setting-animation" class="form-control">
<?php echo $swipe['options']; ?>
</select>
</div>
</div>

<div class="form-group">
<label for="inputEmail" class="col-md-3 control-label">Allowed Swipe animations</label>
<div class="col-md-9">
<ul class="vert-list"><?php echo $swipe['checkboxes']; ?></ul>
</div>
</div>


<div class="form-group">
<label for="inputEmail" class="col-md-3 control-label">Default Interface theme</label>
<div class="col-md-9">
<select id="ui-setting-theme" name="ui-setting-theme" class="form-control">
<?php echo $theme['options']; ?>
</select>
</div>
</div>


<div class="form-group">
<label for="inputEmail" class="col-md-3 control-label">Allowed Interface themes</label>
<div class="col-md-9">
<ul class="vert-list"><?php echo $theme['checkboxes']; ?></ul>
</div>
</div>


<div class="form-group">
<label for="inputEmail" class="col-md-3 control-label">Default Button set</label>
<div class="col-md-9">
<select id="ui-setting-buttons" name="ui-setting-buttons" class="form-control">
<?php echo $button['options']; ?>
</select>
</div>
</div>

<div class="form-group">
<label for="inputEmail" class="col-md-3 control-label">Allowed Button sets</label>
<div class="col-md-9">
<ul class="vert-list"><?php echo $button['checkboxes']; ?></ul>
</div>
</div>

<div class="form-group">
<label for="inputEmail" class="col-md-3 control-label">Photo Tags</label>
<div class="col-md-9">
<select id="ui-photo-tags" name="ui-photo-tags" class="form-control">
<option value="show">show</option>
<option value="hide">hide</option>
</select>
</div>
</div>


<div class="form-group">
<label for="inputEmail" class="col-md-3 control-label">Theme background</label>
<div class="col-md-9">
<select id="ui-setting-bgmode" name="ui-setting-bgmode" class="form-control">
<option value="disabled">disabled</option>
<option value="static">static</option>
<option value="animated">animated</option>
</select>
</div>
</div>


<div class="form-group">
<label for="inputEmail" class="col-md-3 control-label">Lightbox Thumbnails</label>
<div class="col-md-9">
<select id="ui-thumbs" name="ui-thumbs" class="form-control">
<option value="show">show</option>
<option value="hide">hide</option>
</select>
</div>
</div>


</div>
</form>

<script type="text/javascript">
<?php include('js/main-config.js'); ?>

$(document).ready(function(){
$('#ui-setting-animation').val(gal_slide_animation);
$('#ui-setting-theme').val(gal_vars_theme);
$('#ui-setting-bgmode').val(gal_vars_bgmode);
$('#ui-thumbs').val(gal_vars_mini_thumbs);
$('#ui-setting-buttons').val(gal_vars_button_set);
$('#ui-photo-tags').val(gal_vars_tags);
$('#admin-theme').val('<?php echo $default_admin_theme; ?>');
$('#admin-theme-menu').val('<?php echo $default_admin_menu; ?>');
$('#admin-resize-photo').val('<?php echo $mlib_upload_resolution; ?>');

admin_preview_theme();

check_option_boxes('themes', gal_vars_themes);
check_option_boxes('animations', gal_slide_animations);
check_option_boxes('buttons', gal_vars_button_sets);

});


function check_option_boxes(xkey, xarray){
for(var i=0;i<xarray.length;++i){
$('#'+xkey+'-'+xarray[i]).attr('checked', true);
}
}

function admin_preview_theme(){
var xtheme = $('#admin-theme').val();
var navbar = 'navbar-'+$('#admin-theme-menu').val();
$('#admin-theme-css').remove();
$('.navbar-fixed-top').removeClass('navbar-inverse').removeClass('navbar-default').addClass(navbar);
$('body').append('<link id="admin-theme-css" type="text/css" rel="stylesheet" href="'+main_domain+'phpix-imports/adminui/'+xtheme+'.css" />');
}
</script>
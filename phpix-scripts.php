<?php 

if($_GET['q']!=''){
$quality = $_GET['q'];
} else {
$quality = 'full';
}

 ?>
<meta charset="utf-8" />
<link id="gal-favicon" rel="shortcut icon" type="image/jpg" href="<?php echo $gallery_domain; ?>phpix-imports/buttons/flat-color/svg/play.svg"/>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0" />
<script>
gal_domain='<?php echo $gallery_domain; ?>';
albumFILE = '<?php echo $albumFILE; ?>';
gal_quality='<?php echo $quality; ?>';
gal_vars_aid='<?php echo $_GET["aid"]; ?>';
gal_vars_uid='<?php if($_SESSION["PHPix"]==""){echo "0"; $xtag_save_db = "false";} else {echo "1"; $xtag_save_db = "true";} ?>';
var xtag_save_db=<?php echo $xtag_save_db; ?>;
gal_sitekey = '<?php echo $siteKey; ?>';
</script>
<link href="<?php echo $gallery_domain; ?>css/imagehover.min.css" type="text/css" rel="stylesheet" />
<link href="<?php echo $gallery_domain; ?>css/album.css" type="text/css" rel="stylesheet" />
<link href="<?php echo $gallery_domain; ?>css/fast.gallery.css" type="text/css" rel="stylesheet" />
<link href="<?php echo $gallery_domain; ?>css/tagged.css" type="text/css" rel="stylesheet" />
<link href="<?php echo $gallery_domain; ?>css/flex-images.css" type="text/css" rel="stylesheet" />
<link href="<?php echo $gallery_domain; ?>phpix-libs/alert/alert.css" type="text/css" rel="stylesheet" />
<link href="<?php echo $gallery_domain; ?>phpix-libs/pagination/simplePagination.css" type="text/css" rel="stylesheet" />
<link href="<?php echo $gallery_domain; ?>phpix-libs/rcrop/rcrop.min.css" type="text/css" rel="stylesheet" />
<script src="<?php echo $gallery_domain; ?>js/jquery-3.5.1.min.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>js/main-config.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>js/longpress.min.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>js/panzoom.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>phpix-libs/rcrop/rcrop.min.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>phpix-libs/pagination/jquery.simplePagination.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>js/screenfull.min.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>js/flex-images.min.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>js/hotkeys.min.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>js/jquery-visibility.min.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>phpix-libs/alert/alert.js" type="text/javascript"></script>
<script src="<?php echo $gallery_domain; ?>js/custom.js" type="text/javascript"></script>

<script src="<?php echo $gallery_domain; ?>js/tagged.js" type="text/javascript"></script>

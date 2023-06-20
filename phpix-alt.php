<?php 
include('phpix-config.php');

function get_parent($folder, $pstring = 'a'){
global $con, $prefix;
$xdata = mysqli_fetch_assoc(mysqli_query($con, "SELECT `id`, `parent` FROM `".$prefix."albums` WHERE `id`='$folder'"));

if($xdata['id']==''){
return 'error';
} elseif($xdata['id']!='' && $xdata['parent']=='') {
$pstring = $pstring.'.'.$folder;
return $pstring;
} else {
$pstring = $pstring.'.'.$folder;
return get_parent($xdata['parent'], $pstring);
}

}

if(!isset($_GET['u'])){$_GET['u']='';} 
if($_GET['u']!=''){

$data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `".$prefix."uploads` WHERE `url`='".$_GET['u']."' limit 1"));

$url = $gallery_domain.'full/'.$_GET['u'];

$qhd = $gallery_domain.'qhd/'.$_GET['u'];
$axid = get_parent($data['folder']);
echo'<!DOCTYPE html>
<html>
<head><meta property="og:title" content="'.$data['title'].'" />
<meta property="og:url" content="'.$url.'" />
<meta property="og:description" content="'.$data['caption'].'">
<meta property="og:image" content="'.$qhd.'">
</head><body>
<script>document.location.href="'.$gallery_domain.'phpix-album.php?aid='.$axid.'&pic='.$_GET['u'].'";</script>
</body>
';
}

if(!isset($_GET['a'])){$_GET['a']='';}
if($_GET['a']!=''){

$data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `".$prefix."albums` WHERE `id`='".$_GET['a']."' limit 1"));

$qhd = $gallery_domain.'cover/'.$data['thumb'];
$axid = get_parent($_GET['a']);
echo'<!DOCTYPE html>
<html>
<head><meta property="og:title" content="'.$data['title'].'" />
<meta property="og:description" content="'.$data['descr'].'">
<meta property="og:image" content="'.$qhd.'">
</head><body>
<script>document.location.href="'.$gallery_domain.'phpix-album.php?aid='.$axid.'";</script>
</body>
';
}




mysqli_close($con);
?>
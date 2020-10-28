<?php 
include('phpix-config.php');


$type = $_GET['type'];
$quality = $_GET['q'];

$url = urlencode($gallery_domain.''.$quality.'/'.$_GET['pic']);

if($type=='fb'){
$location = 'https://www.facebook.com/sharer/sharer.php?u='.$url;
}

if($type=='tw'){
//header('location:https://www.facebook.com/sharer/sharer.php?u='.$url);
$location = 'https://twitter.com/home?status='.$url;
}

if($type=='gp'){
$location = 'https://plus.google.com/share?url='.$url;
}

if($type=='pi'){
$location = 'https://pinterest.com/pin/create/button/?url=&media='.$url.'&description=';
}

if($type=='wh'){
$zurl = $gallery_domain.'phpix-download.php?q=qhd&f='.$_GET['pic'];
$x = file_get_contents($zurl);
$wh_url = urlencode($gallery_domain.'u/'.$_GET['pic']);
$location = 'https://api.whatsapp.com/send?text='.$wh_url;
}

if($location!=''){
echo"<script>document.location.href = '$location';</script>";
}

 ?>
<?php 
include('config.php');
error_reporting(E_ALL);
//print_r($_GET);

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
$location = 'https://api.whatsapp.com/send?text='.urlencode($url);
}

if($location!=''){
echo"<script>document.location.href = '$location';</script>";
}

 ?>
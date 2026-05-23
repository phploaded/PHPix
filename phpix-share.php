<?php
include('phpix-config.php');
require_once('phpix-media-functions.php');

$allowed_qualities = array('full', '2k', 'fhd', 'hd');
$raw_file = isset($_GET['pic']) ? $_GET['pic'] : '';
$raw_quality = isset($_GET['q']) ? $_GET['q'] : 'full';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$quality = phpix_normalize_media_quality($raw_quality);
$file = phpix_normalize_media_file($raw_file);
$location = '';

if($file == '' || !phpix_is_safe_media_file($raw_file) || !phpix_is_allowed_media_quality($quality, $allowed_qualities)){
	http_response_code(400);
	exit;
}

if(!file_exists(phpix_source_disk_path($file))){
	http_response_code(404);
	exit;
}

if($quality != 'full'){
	phpix_prepare_media_file($quality, $file, 80);
}

$url = urlencode(phpix_media_url($quality, $file));

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
phpix_prepare_media_file('hd', $file, 80);
$wh_url = urlencode($gallery_domain.'u/'.$file);
$location = 'https://api.whatsapp.com/send?text='.$wh_url;
}

if($location!=''){
echo"<script>document.location.href = '$location';</script>";
}

?>

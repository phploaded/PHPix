<?php

include('phpix-config.php');
include('phpix-front-functions.php');

$allowed_qualities = array('full', '2k', 'fhd', 'hd');
$raw_file = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$raw_quality = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$file = phpix_normalize_media_file($raw_file);
$quality = phpix_normalize_media_quality($raw_quality);
$flag = 'error';

if($file == '' || !phpix_is_safe_media_file($raw_file) || !phpix_is_allowed_media_quality($quality, $allowed_qualities)){
	http_response_code(400);
	echo '<script>notify(\'Invalid image request\')</script>';
	exit;
}

$source_file = phpix_source_disk_path($file);
if(!file_exists($source_file)){
	http_response_code(404);
	echo '<script>notify(\'Image not found\')</script>';
	exit;
}

$thumb = get_thumb($file, $quality);
$quality_file = phpix_media_disk_path($quality, $file);

if(file_exists($quality_file)){
	$thumb_path = phpix_thumb_disk_path($file);
	$thumb_size = @getimagesize($thumb_path);
	$full_size = @getimagesize($source_file);
	if(!$thumb_size || !$full_size){
		@unlink($thumb_path);
		$flag = '<script>notify(\'Unable to prepare image\')</script>';
	} else {
		list($thumb_width, $thumb_height) = $thumb_size;
		list($full_width, $full_height) = $full_size;
		$thumb_url = phpix_thumb_url($file);
		$flag = '<li class="item" data-file="'.$file.'" data-fw="'.$full_width.'" data-fh="'.$full_height.'" data-w="'.$thumb_width.'" data-h="'.$thumb_height.'"><a href="'.phpix_media_url($quality, $file).'"><img xsrc="'.$thumb_url.'" src="'.$thumb_url.'"></a></li>';
	}
} else {
	http_response_code(404);
	$flag = '<script>notify(\'Unable to load requested image\')</script>';
}

echo $flag;
// there is no ending php tag

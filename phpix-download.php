<?php

include('phpix-config.php');
require_once('phpix-media-functions.php');

$allowed_qualities = array('full', '2k', 'fhd', 'hd');
$raw_quality = isset($_GET['q']) ? $_GET['q'] : '';
$raw_file = isset($_GET['f']) ? $_GET['f'] : '';
$quality = phpix_normalize_media_quality($raw_quality);
$file = phpix_normalize_media_file($raw_file);
$prepare_only = isset($_GET['prepare']) && $_GET['prepare'] == '1';

if($file == '' || !phpix_is_safe_media_file($raw_file) || !phpix_is_allowed_media_quality($quality, $allowed_qualities)){
	http_response_code(400);
	echo 'Invalid media request.';
	exit;
}

$source_file = phpix_source_disk_path($file);
if(!file_exists($source_file)){
	http_response_code(404);
	echo 'File not found.';
	exit;
}

if(!phpix_prepare_media_file($quality, $file, 80)){
	http_response_code(500);
	echo 'Unable to prepare the requested file.';
	exit;
}

$resolved_quality = phpix_resolve_media_quality($quality, $file);
$target = phpix_media_disk_path($resolved_quality, $file);
if(!file_exists($target)){
	http_response_code(404);
	echo 'File not found.';
	exit;
}

if($prepare_only){
	header('Content-Type: text/plain; charset=UTF-8');
	echo 'ready';
	exit;
}

$mime_type = 'application/octet-stream';
if(function_exists('finfo_open')){
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	if($finfo){
		$detected_type = finfo_file($finfo, $target);
		if($detected_type){
			$mime_type = $detected_type;
		}
		finfo_close($finfo);
	}
} elseif(function_exists('mime_content_type')){
	$detected_type = mime_content_type($target);
	if($detected_type){
		$mime_type = $detected_type;
	}
}

$download_name = str_replace('"', '', phpix_media_filename($file, $resolved_quality));

while(ob_get_level() > 0){
	ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: '.$mime_type);
header('Content-Disposition: attachment; filename="'.$download_name.'"');
header('Content-Length: '.filesize($target));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
readfile($target);
exit;

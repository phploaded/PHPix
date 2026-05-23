<?php

if(!function_exists('phpix_is_webp_quality')){
function phpix_is_webp_quality($quality){
	return in_array($quality, array('2k', 'fhd', 'hd'), true);
}
}

if(!function_exists('phpix_quality_dimension')){
function phpix_quality_dimension($quality){
	$quality_index = array(
	"2k" => 1440,
	"fhd" => 1080,
	"hd" => 720,
	);

	if(isset($quality_index[$quality])){
		return $quality_index[$quality];
	}

	return 0;
}
}

if(!function_exists('phpix_thumb_directory_name')){
function phpix_thumb_directory_name(){
	global $default_gallery_settings;

	if(isset($default_gallery_settings['thumb_dir']) && trim((string) $default_gallery_settings['thumb_dir']) != ''){
		return trim((string) $default_gallery_settings['thumb_dir']);
	}

	return 'thumb';
}
}

if(!function_exists('phpix_thumb_height')){
function phpix_thumb_height($fallback = 300){
	global $default_gallery_settings;

	if(isset($default_gallery_settings['thumb_height']) && (int) $default_gallery_settings['thumb_height'] > 0){
		return (int) $default_gallery_settings['thumb_height'];
	}

	return (int) $fallback;
}
}

if(!function_exists('phpix_thumb_filename')){
function phpix_thumb_filename($file){
	$file_info = pathinfo($file);
	return $file_info['filename'].'.webp';
}
}

if(!function_exists('phpix_thumb_relative_path')){
function phpix_thumb_relative_path($file){
	return phpix_thumb_directory_name().'/'.phpix_thumb_filename($file);
}
}

if(!function_exists('phpix_thumb_disk_path')){
function phpix_thumb_disk_path($file){
	return dirname(__FILE__).'/'.phpix_thumb_relative_path($file);
}
}

if(!function_exists('phpix_legacy_thumb_disk_path')){
function phpix_legacy_thumb_disk_path($file){
	return dirname(__FILE__).'/'.phpix_thumb_directory_name().'/'.pathinfo($file, PATHINFO_BASENAME);
}
}

if(!function_exists('phpix_thumb_url')){
function phpix_thumb_url($file){
	global $gallery_domain;
	return $gallery_domain.phpix_thumb_relative_path($file);
}
}

if(!function_exists('phpix_media_shortest_side')){
function phpix_media_shortest_side($width, $height){
	return min((int) $width, (int) $height);
}
}

if(!function_exists('phpix_media_quality_rank')){
function phpix_media_quality_rank($quality){
	$quality_index = array(
	"hd" => 1,
	"fhd" => 2,
	"2k" => 3,
	"full" => 4,
	);

	if(isset($quality_index[$quality])){
		return $quality_index[$quality];
	}

	return 0;
}
}

if(!function_exists('phpix_allowed_media_qualities')){
function phpix_allowed_media_qualities(){
	return array('full', '2k', 'fhd', 'hd', 'thumb', 'cover', 'ai');
}
}

if(!function_exists('phpix_is_allowed_media_quality')){
function phpix_is_allowed_media_quality($quality, $allowed_qualities = null){
	$quality = strtolower(trim((string) $quality));
	if($allowed_qualities === null){
		$allowed_qualities = phpix_allowed_media_qualities();
	}

	return in_array($quality, $allowed_qualities, true);
}
}

if(!function_exists('phpix_normalize_media_quality')){
function phpix_normalize_media_quality($quality, $fallback = 'full'){
	$quality = strtolower(trim((string) $quality));
	if($quality == ''){
		return $fallback;
	}

	return $quality;
}
}

if(!function_exists('phpix_is_safe_media_file')){
function phpix_is_safe_media_file($file){
	$file = rawurldecode((string) $file);
	$file = trim(str_replace("\0", '', $file));
	if($file == ''){
		return false;
	}

	$normalized = str_replace('\\', '/', $file);
	return $file === basename($normalized);
}
}

if(!function_exists('phpix_normalize_media_file')){
function phpix_normalize_media_file($file){
	$file = rawurldecode((string) $file);
	$file = trim(str_replace("\0", '', $file));
	if($file == ''){
		return '';
	}

	$normalized = str_replace('\\', '/', $file);
	return basename($normalized);
}
}

if(!function_exists('phpix_media_filename')){
function phpix_media_filename($file, $quality = 'full'){
	$file_info = pathinfo($file);

	if($quality == 'ai'){
		return $file_info['filename'].'.webp';
	}

	if(phpix_is_webp_quality($quality)){
		return $file_info['filename'].'.webp';
	}

	return $file_info['basename'];
}
}

if(!function_exists('phpix_media_relative_path')){
function phpix_media_relative_path($quality, $file){
	$quality = phpix_resolve_media_quality($quality, $file);
	return $quality.'/'.phpix_media_filename($file, $quality);
}
}

if(!function_exists('phpix_media_disk_path')){
function phpix_media_disk_path($quality, $file){
	return dirname(__FILE__).'/'.phpix_media_relative_path($quality, $file);
}
}

if(!function_exists('phpix_legacy_media_disk_path')){
function phpix_legacy_media_disk_path($quality, $file){
	return dirname(__FILE__).'/'.$quality.'/'.pathinfo($file, PATHINFO_BASENAME);
}
}

if(!function_exists('phpix_media_url')){
function phpix_media_url($quality, $file){
	global $gallery_domain;
	return $gallery_domain.phpix_media_relative_path($quality, $file);
}
}

if(!function_exists('phpix_source_disk_path')){
function phpix_source_disk_path($file){
	return dirname(__FILE__).'/full/'.phpix_normalize_media_file($file);
}
}

if(!function_exists('phpix_ai_disk_path')){
function phpix_ai_disk_path($file){
	return dirname(__FILE__).'/ai/'.phpix_media_filename($file, 'ai');
}
}

if(!function_exists('phpix_source_dimensions')){
function phpix_source_dimensions($file){
	static $dimension_cache = array();

	$file = phpix_normalize_media_file($file);
	if($file == ''){
		return false;
	}

	if(array_key_exists($file, $dimension_cache)){
		return $dimension_cache[$file];
	}

	$source_file = dirname(__FILE__).'/full/'.$file;
	$info = @getimagesize($source_file);
	if(!$info){
		$dimension_cache[$file] = false;
		return false;
	}

	$dimension_cache[$file] = array((int) $info[0], (int) $info[1]);
	return $dimension_cache[$file];
}
}

if(!function_exists('phpix_max_generated_quality_for_dimensions')){
function phpix_max_generated_quality_for_dimensions($width, $height){
	$shortest_side = phpix_media_shortest_side($width, $height);

	if($shortest_side >= phpix_quality_dimension('2k')){
		return '2k';
	}

	if($shortest_side >= phpix_quality_dimension('fhd')){
		return 'fhd';
	}

	if($shortest_side >= phpix_quality_dimension('hd')){
		return 'hd';
	}

	return '';
}
}

if(!function_exists('phpix_resolve_media_quality')){
function phpix_resolve_media_quality($quality, $file){
	$quality = phpix_normalize_media_quality($quality);
	if($quality == 'full' || !phpix_is_webp_quality($quality)){
		return $quality;
	}

	$dimensions = phpix_source_dimensions($file);
	if(!$dimensions){
		return $quality;
	}

	$max_quality = phpix_max_generated_quality_for_dimensions($dimensions[0], $dimensions[1]);
	if($max_quality === ''){
		return 'full';
	}

	if(phpix_media_quality_rank($quality) > phpix_media_quality_rank($max_quality)){
		return $max_quality;
	}

	return $quality;
}
}

if(!function_exists('phpix_open_image_resource')){
function phpix_open_image_resource($file, $image_type = null){
	if($image_type === null){
		$info = @getimagesize($file);
		if(!$info){
			return false;
		}
		$image_type = $info[2];
	}

	if($image_type == IMAGETYPE_JPEG){
		return @imagecreatefromjpeg($file);
	}

	if($image_type == IMAGETYPE_PNG){
		return @imagecreatefrompng($file);
	}

	if($image_type == IMAGETYPE_GIF){
		return @imagecreatefromgif($file);
	}

	if($image_type == IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')){
		return @imagecreatefromwebp($file);
	}

	return false;
}
}

if(!function_exists('phpix_generate_webp_variant')){
function phpix_generate_webp_variant($source_file, $target_file, $quality, $compression = 80){
	if(!function_exists('imagewebp') || !file_exists($source_file)){
		return false;
	}

	$dimension = phpix_quality_dimension($quality);
	if($dimension < 1){
		return false;
	}

	$target_dir = dirname($target_file);
	if(!is_dir($target_dir)){
		mkdir($target_dir, 0777, true);
	}
	if(!file_exists($target_dir.'/index.html')){
		file_put_contents($target_dir.'/index.html', '');
	}

	$info = @getimagesize($source_file);
	if(!$info){
		return false;
	}

	$source = phpix_open_image_resource($source_file, $info[2]);
	if(!$source){
		return false;
	}

	$src_width = $info[0];
	$src_height = $info[1];
	$shortest_side = phpix_media_shortest_side($src_width, $src_height);
	if($shortest_side < 1){
		imagedestroy($source);
		return false;
	}
	$scale = min(1, ($dimension / $shortest_side));
	$target_width = max(1, (int) round($src_width * $scale));
	$target_height = max(1, (int) round($src_height * $scale));

	$canvas = imagecreatetruecolor($target_width, $target_height);
	imagealphablending($canvas, false);
	imagesavealpha($canvas, true);
	$transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
	imagefilledrectangle($canvas, 0, 0, $target_width, $target_height, $transparent);

	imagecopyresampled($canvas, $source, 0, 0, 0, 0, $target_width, $target_height, $src_width, $src_height);
	$result = imagewebp($canvas, $target_file, $compression);

	imagedestroy($canvas);
	imagedestroy($source);

	$legacy_file = phpix_legacy_media_disk_path($quality, basename($source_file));
	if($legacy_file !== $target_file && file_exists($legacy_file)){
		unlink($legacy_file);
	}

	return $result;
}
}

if(!function_exists('phpix_generate_webp_thumbnail')){
function phpix_sharpen_canvas($canvas){
	if(!function_exists('imageconvolution')){
		return;
	}

	$sharpen_matrix = array(
		array(-1, -1, -1),
		array(-1, 16, -1),
		array(-1, -1, -1),
	);

	imageconvolution($canvas, $sharpen_matrix, 8, 0);
}
}

if(!function_exists('phpix_generate_webp_thumbnail')){
function phpix_generate_webp_thumbnail($source_file, $target_file, $target_height = 300, $compression = 90){
	if(!function_exists('imagewebp') || !file_exists($source_file)){
		return false;
	}

	$target_height = (int) $target_height;
	if($target_height < 1){
		return false;
	}

	$target_dir = dirname($target_file);
	if(!is_dir($target_dir)){
		mkdir($target_dir, 0777, true);
	}
	if(!file_exists($target_dir.'/index.html')){
		file_put_contents($target_dir.'/index.html', '');
	}

	$info = @getimagesize($source_file);
	if(!$info){
		return false;
	}

	$source = phpix_open_image_resource($source_file, $info[2]);
	if(!$source){
		return false;
	}

	$src_width = $info[0];
	$src_height = $info[1];
	if($src_height < 1){
		imagedestroy($source);
		return false;
	}

	$scale = min(1, ($target_height / $src_height));
	$target_width = max(1, (int) round($src_width * $scale));
	$target_height = max(1, (int) round($src_height * $scale));

	$canvas = imagecreatetruecolor($target_width, $target_height);
	imagealphablending($canvas, false);
	imagesavealpha($canvas, true);
	$transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
	imagefilledrectangle($canvas, 0, 0, $target_width, $target_height, $transparent);

	imagecopyresampled($canvas, $source, 0, 0, 0, 0, $target_width, $target_height, $src_width, $src_height);
	if($scale < 1){
		phpix_sharpen_canvas($canvas);
	}
	$result = imagewebp($canvas, $target_file, $compression);

	imagedestroy($canvas);
	imagedestroy($source);

	$legacy_file = phpix_legacy_thumb_disk_path(basename($source_file));
	if($legacy_file !== $target_file && file_exists($legacy_file)){
		unlink($legacy_file);
	}

	return $result;
}
}

if(!function_exists('phpix_prepare_media_file')){
function phpix_prepare_media_file($quality, $file, $compression = 80){
	$file = phpix_normalize_media_file($file);
	if($file == ''){
		return false;
	}

	$quality = phpix_resolve_media_quality($quality, $file);

	$source_file = phpix_source_disk_path($file);
	if(!file_exists($source_file)){
		return false;
	}

	if($quality == 'full'){
		return true;
	}

	if(!phpix_is_webp_quality($quality)){
		return false;
	}

	$target_file = phpix_media_disk_path($quality, $file);
	if(file_exists($target_file)){
		return true;
	}

	return phpix_generate_webp_variant($source_file, $target_file, $quality, $compression);
}
}

if(!function_exists('phpix_prepare_thumb_file')){
function phpix_prepare_thumb_file($file, $compression = 90, $target_height = null){
	$file = phpix_normalize_media_file($file);
	if($file == ''){
		return false;
	}

	$source_file = phpix_source_disk_path($file);
	if(!file_exists($source_file)){
		return false;
	}

	if($target_height === null){
		$target_height = phpix_thumb_height();
	}
	$target_height = (int) $target_height;
	if($target_height < 1){
		$target_height = phpix_thumb_height();
	}

	$target_file = phpix_thumb_disk_path($file);
	if(file_exists($target_file)){
		$legacy_file = phpix_legacy_thumb_disk_path($file);
		if($legacy_file !== $target_file && file_exists($legacy_file)){
			unlink($legacy_file);
		}
		return true;
	}

	return phpix_generate_webp_thumbnail($source_file, $target_file, $target_height, $compression);
}
}

if(!function_exists('phpix_backup_media_selection')){
function phpix_backup_media_selection($quality, $file, $compression = 80){
	$file = phpix_normalize_media_file($file);
	$quality = phpix_normalize_media_quality($quality);
	if($file == ''){
		return false;
	}

	$source_file = phpix_source_disk_path($file);
	if(!file_exists($source_file)){
		return false;
	}

	if($quality == 'full'){
		return array(
			'disk_path' => $source_file,
			'archive_filename' => pathinfo($file, PATHINFO_BASENAME),
			'quality' => 'full',
			'is_original' => true,
		);
	}

	if($quality == 'thumb' || $quality == 'cover'){
		$target_file = dirname(__FILE__).'/'.$quality.'/'.pathinfo($file, PATHINFO_BASENAME);
		if(!file_exists($target_file)){
			return false;
		}

		return array(
			'disk_path' => $target_file,
			'archive_filename' => pathinfo($file, PATHINFO_BASENAME),
			'quality' => $quality,
			'is_original' => false,
		);
	}

	if($quality == 'ai'){
		$target_file = phpix_ai_disk_path($file);
		if(!file_exists($target_file)){
			return false;
		}

		return array(
			'disk_path' => $target_file,
			'archive_filename' => phpix_media_filename($file, 'ai'),
			'quality' => 'ai',
			'is_original' => false,
		);
	}

	if(!phpix_is_webp_quality($quality)){
		return false;
	}

	$dimensions = phpix_source_dimensions($file);
	if($dimensions){
		$shortest_side = phpix_media_shortest_side($dimensions[0], $dimensions[1]);
		$requested_dimension = phpix_quality_dimension($quality);

		if($requested_dimension > 0 && $shortest_side <= $requested_dimension){
			return array(
				'disk_path' => $source_file,
				'archive_filename' => pathinfo($file, PATHINFO_BASENAME),
				'quality' => 'full',
				'is_original' => true,
			);
		}
	}

	if(!phpix_prepare_media_file($quality, $file, $compression)){
		return false;
	}

	$target_file = phpix_media_disk_path($quality, $file);
	if(!file_exists($target_file)){
		return false;
	}

	return array(
		'disk_path' => $target_file,
		'archive_filename' => phpix_media_filename($file, $quality),
		'quality' => $quality,
		'is_original' => false,
	);
}
}

if(!function_exists('phpix_delete_generated_versions')){
function phpix_delete_generated_versions($file){
	foreach(array('2k', 'fhd', 'hd') as $quality){
		$target = phpix_media_disk_path($quality, $file);
		if(file_exists($target)){
			unlink($target);
		}

		$legacy_file = phpix_legacy_media_disk_path($quality, $file);
		if($legacy_file !== $target && file_exists($legacy_file)){
			unlink($legacy_file);
		}
	}
}
}

if(!function_exists('phpix_delete_media_bundle')){
function phpix_delete_media_bundle($file){
	$file = phpix_normalize_media_file($file);
	if($file == ''){
		return;
	}

	$source_file = phpix_source_disk_path($file);
	if(file_exists($source_file)){
		unlink($source_file);
	}

	$thumb_file = phpix_thumb_disk_path($file);
	if(file_exists($thumb_file)){
		unlink($thumb_file);
	}

	$legacy_thumb_file = phpix_legacy_thumb_disk_path($file);
	if($legacy_thumb_file !== $thumb_file && file_exists($legacy_thumb_file)){
		unlink($legacy_thumb_file);
	}

	$ai_file = phpix_ai_disk_path($file);
	if(file_exists($ai_file)){
		unlink($ai_file);
	}

	phpix_delete_generated_versions($file);
}
}

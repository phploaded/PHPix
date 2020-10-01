<?php 

function convertToReadableSize($size){
  $base = log($size) / log(1024);
  $suffix = array("", " KB", " MB", " GB", " TB");
  $f_base = floor($base);
  return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
}

function gal_html_tags($text, $prefix_url){
$text = str_replace(' ', '', $text);
$tags = explode(',', $text);

$str = '';
foreach($tags as $tag){
	if($tag!=''){
	$str = $str.'<li><a href="'.$prefix_url.''.$tag.'">'.$tag.'</a></li>';
	}
}

if($str==''){$str='<i>No tags</i>';}

return $str;
} 

function get_thumb($path, $quality='full'){
global $xthumb_secret;
global $gallery_domain;
global $default_gallery_settings;
$file_info = pathinfo($path);
$thumb_file_name = $file_info['basename'];
if(!file_exists($default_gallery_settings['thumb_dir'].'/'.$thumb_file_name)){
$thumb_file_data = file_get_contents($gallery_domain.'xthumb-'.$xthumb_secret.'.php?src='.urlencode($gallery_domain.'full/'.$path).'&h='.$default_gallery_settings['thumb_height'].'&q=90&s=1');
$fp = fopen($default_gallery_settings['thumb_dir'].'/'.$thumb_file_name, "w");
fwrite($fp, $thumb_file_data);
fclose($fp);
}

$quality_index = array(
"qhd" => "480",
"hd" => "720",
"fhd" => "1080"
);

if(!file_exists($quality.'/'.$thumb_file_name) && $quality!='full'){

$file = getimagesize('full/'.$thumb_file_name);
$width = $file[0];
$height = $file[1];

if($width>$height){
$thumb_file_data = file_get_contents($gallery_domain.'xthumb-'.$xthumb_secret.'.php?src='.urlencode($gallery_domain.'full/'.$path).'&h='.$quality_index[$quality].'&q=90');
} else {
$thumb_file_data = file_get_contents($gallery_domain.'xthumb-'.$xthumb_secret.'.php?src='.urlencode($gallery_domain.'full/'.$path).'&w='.$quality_index[$quality].'&q=90');
}

//echo $gallery_domain.'xthumb.php?src='.$path;
$fp = fopen($quality.'/'.$thumb_file_name, "w");
fwrite($fp, $thumb_file_data);
fclose($fp);
}

return $thumb_file_name;
}




function rrmdir($dir) {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (filetype($dir."/".$object) == "dir") 
           rrmdir($dir."/".$object); 
        else unlink   ($dir."/".$object);
      }
    }
    reset($objects);
    rmdir($dir);
  }
 }


function quick_paginate($numrows){

if($_GET['ipp']>0){
$per_page = $_GET['ipp'];
} else {
$per_page = 10;
}

if($_GET['pagenumber']>0){
$paginate['current'] = $_GET['pagenumber'];
$start = ($_GET['pagenumber']-1)*$per_page;
} else {
$paginate['current'] = 1;
$start = 0;
}

$paginate[start] = $start;
$paginate[per_page] = $per_page;

return $paginate;
}
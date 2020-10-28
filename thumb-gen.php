<?php 

include('phpix-config.php');
include('phpix-front-functions.php');
$file = $_REQUEST['id'];
$quality = $_REQUEST['q'];
$thumb = get_thumb($file, $quality);
$flag = 'error';

//echo $gallery_domain.'full/'.$file;
if(file_exists($quality.'/'.$thumb)){
list($thumb_width, $thumb_height) = getimagesize($default_gallery_settings['thumb_dir'].'/'.$thumb);
	if($thumb_width<1 && $thumb_height<1){
	unlink($thumb);
	$flag = '<script>notify(\'Error for '.$file.'\')</script>';
	} else {
	$flag = '<li class="item" data-w="'.$thumb_width.'" data-h="'.$thumb_height.'"><a href="'.$gallery_domain.''.$quality.'/'.$file.'"><img xsrc="'.$gallery_domain.'thumb/'.$thumb.'" src="'.$gallery_domain.'thumb/'.$thumb.'"></a></li>';
	}
} else {
$flag = '<script>notify(\'Cant find '.$thumb.'\')';
}
echo $flag;
// there is no ending php tag
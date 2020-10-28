<?php
/* ============================ CONFIGURATION START ============================= */

include('../phpix-config.php');

/* This is a relative filepath where you have kept this file */
define('MLIBPATH', str_replace('medialibv2', '', dirname(__FILE__)) );

/* This is a relative URL where you have kept this file */
define('MLIBURL', $domain);
/* database connection settings - host, username, password, database */
//$mlib_db = new mysqli("localhost","root","","phpix");
$mlib_db = $con;

/* Table Prefixes of this plugin, if you dont know what it is, do not change anything */
define('MLIBPREFIX', $prefix);

/* If you want to use it for multiple people, then you must fetch the user id in your user system instead of 1. If you are using this plugin just for 1 admin, there is no need to do anything */

if($_SESSION['phpixuser']!=''){ $mlib_current_user = $_SESSION['phpixuser']; } 
else { $mlib_current_user = 1; } 

/* image and file extensions to be allowed. If any extention is not list here, it will not get uploaded even if you allow via javascript in webpages. */
$mlib_allowed_images = array("jpg", "jpeg", "png", "gif");
$mlib_allowed_images_mime = array("image/jpg", "image/jpeg", "image/png", "image/gif");
$mlib_allowed_filetypes = array("txt", "pdf", "doc", "docx", "ppt", "zip", "rar");



/* ============================= CONFIGURATION END =============================== */




if($mlib_db->connect_errno > 0){
    die('<h1>Unable to connect to database [' . $mlib_db->connect_error . ']</h1>');
}



function format_tags($text){
$text = trim($text);
//Remove any character that isn't A-Z, a-z, 0-9, a space, hypheen, underscore or a full stop.
$text = preg_replace("/[^A-Za-z0-9.\-_,]/", '', $text);
$text_arr = explode(',', $text);
$b_arr = array_unique($text_arr);

foreach($b_arr as $key => $val) 
{ 
    if($val === '') 
    { 
        unset($b_arr[$key]); 
    } 
}

$text = implode(', ', $b_arr);
return $text;
}



function get_image_thumb($thumb, $query){
$domain = MLIBURL;
global $xthumb_secret;

if(file_exists('../thumb/'.$thumb)){
return $thumb;
} else {
$get_file1 = file_get_contents($domain.'xthumb-'.$xthumb_secret.'.php?src='.$domain.'full/'.$thumb.'&q=90&s=1&'.$query);
$new_file1 = fopen('../thumb/'.$thumb, "w");
fwrite($new_file1, $get_file1);
fclose($new_file1);
return $thumb;
}

}

// https://www.youtube.com/watch?v=odEZtGYKzFU
function is_yt_URL($url){
$xurl = explode("watch?v=", $url);
if($xurl[1]!=''){return true;} else {return false;}
}



/* $file_id is new filename without extention */
function upload_from_url($url, $file_id, $file_ext='none'){
global $mlib_allowed_images_mime;
$domain = MLIBURL;
$fname = pathinfo($url);
$fname['extension'] = '';

$title = slugify($fname['filename']);
$file_name = $file_id.'.'.strtolower($fname['extension']);
$get_file1 = file_get_contents($url);

//die($fname['extension']);
$new_file1 = fopen('../full/'.$file_name, "w");
fwrite($new_file1, $get_file1);
fclose($new_file1);

$data['mime'] = mime_content_type('../full/'.$file_name);

if(in_array($data['mime'], $mlib_allowed_images_mime)){
	
		$ext = str_replace('image/', '', $data['mime']);

		// rename file if no extention was given earlier
		if($fname['extension']==''){
		rename('../full/'.$file_name, '../full/'.$file_name.$ext);
		$data['fname'] = $file_name.$ext;
		$data['size'] = filesize('../full/'.$file_name.$ext);
		} else {
		$data['fname'] = $file_name;
		$data['size'] = filesize('../full/'.$file_name);
		}

} else {
$ext = strtolower($fname['extension']);
$data['fname'] = $file_name;
$data['size'] = filesize('../full/'.$file_name);
}


$data['id'] = $title;
$data['title'] = $fname['filename'];
$data['ext'] = $ext;


return $data;
}


function file_from_data($data, $file_id, $ext, $folder = 'full/'){
$file_name = $file_id.'.'.strtolower($ext);

list($type, $data) = explode(';', $data);
list(, $data)      = explode(',', $data);
$data = base64_decode($data);

return file_put_contents('../'.$folder.''.$file_name, $data);
}



function slugify($text, $timestamp='yes'){ 
  // replace non letter or digits by -
  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

  // trim
  $text = trim($text, '-');

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // lowercase
  $text = strtolower($text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  if (empty($text))
  {
    return 'n-a';
  }

if($timestamp=='yes'){return $text.'-'.uniqid();} else { return $text; }
}


function directory_size($path){ /*--------Gives folder size in bytes--------*/
if(!file_exists($path)) return 0;
if(is_file($path)) return filesize($path);
$ret = 0;
foreach(glob($path."/*") as $fn)
$ret += directory_size($fn);
return $ret;
} 


function display_size($size) { /*-----------Gives size in mb,kb,gb from bytes-------------*/
$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
if ($retstring === null) { $retstring = '%01.2f %s'; }
$lastsizestring = end($sizes);
foreach ($sizes as $sizestring) {
if ($size < 1024) { break; }
if ($sizestring != $lastsizestring) { $size /= 1024; }
}
if ($sizestring == $sizes[0]) { $retstring = '%01d %s'; } 
return sprintf($retstring, $size, $sizestring);
}

function empty_directory($dir){ /*-----------Deletes all files in folder, path must end with slash-------------*/
foreach(glob($dir.'*.*') as $v){
unlink($v);
}
}


function mlib_delete_file($file){
if(file_exists($file)){
unlink($file);
}
}
?>
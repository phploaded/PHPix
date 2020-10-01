<?php session_start();

include('mlib-config.php');
$userid = $mlib_current_user;
$ds          = '/';
$storeFolder = 'mlib-uploads/full/';
 
if (!empty($_FILES)) {
$num_files = count($_FILES['file']['tmp_name']);
for($i=0; $i < $num_files;$i++){
$tempFile = $_FILES['file']['tmp_name'][$i]; 
$size = $_FILES['file']['size'][$i]; 
$fname = pathinfo($_FILES['file']['name'][$i]);

$targetPath = $storeFolder;
$ext = strtolower($fname['extension']);
$id = slugify($fname['filename']);
$newfilename = $id.".".$ext;
$targetFile =  $targetPath. $newfilename;

if(move_uploaded_file($tempFile,$targetFile)){

$type = slugify($ext);
$title = htmlentities($fname['filename'], ENT_QUOTES, "UTF-8");
$title = str_replace('_', ' ', $title);
$title = trim(str_replace('-', ' ', $title));
$caption = $title;
$url = MLIBURL.$targetFile;

if(in_array($ext, $mlib_allowed_images)){
$thumb = get_image_thumb($newfilename, 'w=150&h=150');
mysqli_query($mlib_db, "INSERT INTO `".MLIBPREFIX."uploads` (`id`, `type`, `title`, `caption`, `url`, `thumb`, `time`, `uid`, `size`) 
VALUES ('$id', '$ext', '$title', '$caption', '$url', '$thumb', '".time()."', '$userid', '$size')");
} else {

if(file_exists('mlib-includes/icons/100px/'.$ext.'.png')){
$thumb = MLIBURL.'mlib-includes/icons/100px/'.$ext.'.png';
} else {
$thumb = MLIBURL.'mlib-includes/icons/100px/blank.png';
}

mysqli_query($mlib_db, "INSERT INTO `".MLIBPREFIX."uploads` (`id`, `type`, `title`, `caption`, `url`, `thumb`, `time`, `uid`, `size`) 
VALUES ('$id', '$ext', '$title', '$caption', '$url', '$thumb', '".time()."', '$userid', '$size')");
}






}


}

if($_POST["mlib_manual"]=="yes"){
echo'<script>
//window.top.mlib_thumbs_after_upload();
window.location.href=\'mlib-iframe.php?init=1\';</script>';
}

}

?>
<?php 

include('mlib-config.php');
$userid = $mlib_current_user;
$ds          = '/';
$storeFolder = '../full/';
$folder = $_GET['fid'];
 
if (!empty($_FILES)) {

$tempFile = $_FILES['file']['tmp_name']; 
$size = $_FILES['file']['size']; 
$fname = pathinfo($_FILES['file']['name']);

//$fi = new FilesystemIterator($storeFolder, FilesystemIterator::SKIP_DOTS);
//$total_files = iterator_count($fi);

$targetPath = $storeFolder;
$ext = strtolower($fname['extension']);
//$id = slugify($fname['filename'], 'no');
//$id = uniqid();
$id = uniqid();
$newfilename = $id.".".$ext;
$targetFile =  $targetPath. $newfilename;

if(move_uploaded_file($tempFile,$targetFile)){

$type = slugify($ext);
$title = htmlentities($fname['filename'], ENT_QUOTES, "UTF-8");
$title = str_replace('_', ' ', $title);
$title = trim(str_replace('-', ' ', $title));
$caption = $title;
//$url = $targetFile;

if(in_array($ext, $mlib_allowed_images)){
$thumb = get_image_thumb($newfilename, 'h=150');
mysqli_query($mlib_db, "INSERT INTO `".MLIBPREFIX."uploads` (`id`, `type`, `title`, `folder`, `caption`, `url`, `thumb`, `time`, `uid`, `size`) 
VALUES ('$id', '$ext', '$title', '$folder', '$caption', '$newfilename', '$thumb', '".time()."', '$userid', '$size')");
} else {

if(file_exists('mlib-includes/icons/100px/'.$ext.'.png')){
$thumb = MLIBURL.'mlib-includes/icons/100px/'.$ext.'.png';
} else {
$thumb = MLIBURL.'mlib-includes/icons/100px/blank.png';
}

mysqli_query($mlib_db, "INSERT INTO `".MLIBPREFIX."uploads` (`id`, `type`, `title`, `folder`, `caption`, `url`, `thumb`, `time`, `uid`, `size`) 
VALUES ('$id', '$ext', '$title', '$folder', '$caption', '$url', '$thumb', '".time()."', '$userid', '$size')");
}






}

if(isset($_POST["mlib_manual"])){
if($_POST["mlib_manual"]=="yes"){
echo'<script>
//window.top.mlib_thumbs_after_upload();
window.location.href=\'mlib-iframe.php?init=1\';</script>';
}}

}

?>
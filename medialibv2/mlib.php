<?php 

include('mlib-config.php');

if($mlib_current_user!=''){
$method = $_POST['func'];

/* creates a proper list of emails from input text. Removed unwanted chars, lines, spaces, etc */
function clean_emails($emails){
$emails = str_replace("\r\n", " ", $emails);
$emails = str_replace("\n", " ", $emails);
$emails = str_replace("  ", " ", $emails);
$emails = str_replace(" ", ",", $emails);

$ids = explode(",", $emails);

foreach($ids as $key => $email){
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
unset($ids[$key]);
}
}

$emails = implode(",", $ids);

return $emails;
}

if($method == ''){
die('No direct access. No access identifier found.');
}


if($method == 'mlib_photo_rotate'){

$dirs = array("full");

$parts = pathinfo($_POST['u']);
$newid = uniqid();


foreach($dirs as $dir){
$filename = '../'.$dir.'/' . $_POST['u'];
$newfile = $newid.'.'.$parts['extension'];
$newname = '../'.$dir.'/'.$newfile;
if(file_exists($filename)){

$degrees = 360-$_POST['deg'];
//$postid = $_POST['postid'];

$mime = mime_content_type($filename);

// Content type
header('Content-type: '.$mime);

// Load
$source = imagecreatefromjpeg($filename);

// Rotate
$rotate = imagerotate($source, $degrees, 0);

imagejpeg($rotate, $newname);
// Free the memory
imagedestroy($source);
imagedestroy($rotate);
}
}

@unlink('../full/' . $_POST['u']);
@unlink('../thumb/' . $_POST['u']);
@unlink('../qhd/' . $_POST['u']);
@unlink('../hd/' . $_POST['u']);
@unlink('../fhd/' . $_POST['u']);

get_image_thumb($newfile, 'h=150');

$sql = "UPDATE `".$prefix."uploads` SET `url`='$newfile', `thumb`='$newfile' WHERE `id`='".$_POST['id']."'";
mysqli_query($con, $sql);

echo $newfile;
}



if($method == 'mlib_set_cover'){
$aid = $_POST['aid'];
$url = $_POST['photo'];
$nurl = str_replace('data:', '', $url);
$uparts = explode(';', $nurl);
$mime = $uparts[0];
$uparts2 = explode('/', $mime);
$ext = $uparts2[1];
$fid = $aid;
$filename = $fid.".".$ext;
if(in_array($mime, $mlib_allowed_images_mime)){
file_from_data($url, $fid, $ext, 'cover/');
}


$photo = $_POST['photo'];
mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."albums` SET `thumb`='$filename' WHERE `id`='$aid'");
echo $filename;
}


if($method == 'mlib_update_album_count'){
$aid = $_POST['aid'];
$ct = mysqli_fetch_assoc(mysqli_query($mlib_db, "SELECT COUNT(*) as `total` FROM `".MLIBPREFIX."uploads` WHERE `folder`='$aid'"));
$count = $ct['total'];
mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."albums` SET `count`='$count' WHERE `id`='$aid'");
echo $count;
}


if($method == 'mlib_change_album'){

$xto = $_POST['xto'];
$xfrom = $_POST['xfrom'];
$xphoto = $_POST['xphoto'];

mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."uploads` SET `folder`='$xto' WHERE `id`='$xphoto'");
mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."albums` SET count = count - 1 WHERE `id`='$xfrom'");
mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."albums` SET count = count + 1 WHERE `id`='$xto'");
}

if($method == 'mlib_move_items'){

$xto = $_POST['xto'];
$xfrom = $_POST['xfrom'];

$xsql = '';
foreach($_POST['mlibid'] as $key => $val){
$xsql = $xsql." OR `id`='".$val."'";
}

$count = count($_POST['mlibid']);

mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."uploads` SET `folder`='$xto' WHERE `id`='xyz'".$xsql);
mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."albums` SET count = count - ".$count." WHERE `id`='$xfrom'");
mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."albums` SET count = count + ".$count." WHERE `id`='$xto'");
echo $count.' Files moved!';
}


if($method == 'load_thumbs'){

if(!isset($_REQUEST['ipp'])){$_REQUEST['ipp']='';} 
if(!isset($_REQUEST['sort'])){$_REQUEST['sort']='';} 
if(!isset($_REQUEST['page'])){$_REQUEST['page']='';} 
if($_REQUEST['ipp']==''){$ipp=30;} else {$ipp=$_REQUEST['ipp'];}
if($_REQUEST['sort']==''){$xsort='time-DESC';} else {$xsort=$_REQUEST['sort'];}
if($_REQUEST['page']==''){$page=0;} else {$page=$_REQUEST['page']-1;}

$sort = explode("-", $xsort);

$limit = $page * $ipp;

$i=0;
$data = array();
$complete = mysqli_fetch_assoc(mysqli_query($mlib_db, "SELECT COUNT(*) as `gtotal` FROM `".MLIBPREFIX."uploads` WHERE `uid`='$mlib_current_user' AND `folder`='".$_GET['fid']."'"));
$qry = "SELECT * FROM `".MLIBPREFIX."uploads` WHERE `uid`='$mlib_current_user' AND `folder`='".$_GET['fid']."' ORDER BY `".$sort[0]."` ".$sort[1]." limit $limit, $ipp";
$res = mysqli_query($mlib_db, $qry);

while($row = mysqli_fetch_assoc($res)){
$row['newtime'] = date("l, jS M Y, h:i:s a", $row['time']);

$data[] = $row;
++$i;
}

$data['total'] = $i;
$data['page'] = $page+1;
$data['ipp'] = $ipp;
$data['sort'] = $xsort;
$data['gtotal'] = $complete['gtotal'];
echo json_encode($data);
}

if($method == 'url_upload'){

$urls = explode("\n", $_POST['urls']);
$folder = $_REQUEST['fid'];

foreach($urls as $url){
$url = trim($url);
$ctype="upload";
$file_id = uniqid();

if (filter_var($url, FILTER_VALIDATE_URL) && strlen($url)>5) {


if(is_yt_URL($url)){
$video_arr = explode("?v=", $url);
$video_arr2 = explode("&", $video_arr[1]);
$video_id = $video_arr2[0];
$url="http://img.youtube.com/vi/".$video_id."/maxresdefault.jpg";
$ctype="youtube";
$file_id = 'yt['.$video_id.']'.uniqid();
}

	
	$data = upload_from_url($url, $file_id);

	if (in_array($data['mime'], $mlib_allowed_images_mime)){

		$file = pathinfo($url);
		$thumb = get_image_thumb($data['fname'], 'h=150');
		$full_url = MLIBURL.'full/'.$data['fname'];
		mysqli_query($mlib_db, "INSERT INTO `".MLIBPREFIX."uploads` (`id`, `type`, `title`, `folder`, `caption`, `url`, `thumb`, `time`, `uid`, `size`, `ctype`) 
		VALUES ('".$file_id."', '".$data['ext']."', '".$data['title']."', '$folder', '".$data['title']."', '".$data['fname']."', '$thumb', '".time()."', '$mlib_current_user', '".$data['size']."', '".$ctype."')");

		echo'<b>Success : </b><i>'.$full_url.'</i> was uploaded.<br /><script>mlib_uploaded_preview(\''.$thumb.'\')</script>';
	} elseif(in_array($ext, $mlib_allowed_filetypes)){
		$data = upload_from_url($url);
		if(file_exists('mlib-includes/icons/100px/'.$data['ext'].'.png')){
			$thumb = MLIBURL.'mlib-includes/icons/100px/'.$data['ext'].'.png';
		} else {
			$thumb = MLIBURL.'mlib-includes/icons/100px/blank.png';
		}
		$url = MLIBURL.'mlib-uploads/full/'.$data['fname'];
		mysqli_query($mlib_db, "INSERT INTO `".MLIBPREFIX."uploads` (`id`, `type`, `title`, `caption`, `url`, `thumb`, `time`, `size`) 
		VALUES ('".$data['id']."', '".$data['ext']."', '".$data['title']."', '".$data['title']."', '".$url."', '$thumb', '".time()."', '1')");
		echo'<b>Success : </b><i>'.$url.'</i> was uploaded.<br /><script>mlib_uploaded_preview(\''.$thumb.'\')</script>';
	} else {
		unlink('../full/'.$data['fname']);
		echo '<b>Error : </b>This is not a valid file format. Transfer Aborted.<br />';
	}

/* for images with data: protocol */
} elseif(strpos($url, "data:") === 0){
	$nurl = str_replace('data:', '', $url);
	$uparts = explode(';', $nurl);
	$mime = $uparts[0];
	$uparts2 = explode('/', $mime);
	$ext = $uparts2[1];
	$fid = uniqid();
	$filename = $fid.".".$ext;
	if(in_array($mime, $mlib_allowed_images_mime)){
		if(file_from_data($url, $fid, $ext)){
			$thumb = get_image_thumb($filename, 'h=150');
			$size = filesize('../full/'.$filename);
			mysqli_query($mlib_db, "INSERT INTO `".MLIBPREFIX."uploads` (`id`, `type`, `title`, `folder`, `caption`, `url`, `thumb`, `time`, `uid`, `size`) 
			VALUES ('".$fid."', '".$ext."', 'phpix ".$fid."', '$folder', 'phpix ".$fid."', '".$filename."', '$thumb', '".time()."', '$mlib_current_user', '".$size."')");
			echo '<b>'.$filename.'</b> : was created.';
		} else {
			echo '<b>'.$filename.'</b> : could not be written to disk. Check permissions or directory settings.';
		}
		
	} else {
		/* invalid DATA */
		echo '<b>'.$mlib_allowed_images_mime[0].'</b> : Data is malformed, corrupted, missing or not an image.';
	}
} else {
	/* invalid URL */
	echo '<b>'.$url.'</b> : This URL cant be uploaded.';
}

}

echo '<br /><b>Processing is complete.</b><script>mlib_refresh();</script><br />';

}


if($method=='mlib_delete_items'){
$i=0;
foreach($_POST['mlibid'] as $key => $val){
$sql = "SELECT * FROM `".MLIBPREFIX."uploads` WHERE `id`='".$val."' AND `uid`='".$mlib_current_user."'";
$data = mysqli_fetch_assoc(mysqli_query($mlib_db, $sql));

/* delete full image and thumb */
if($mlib_current_user==$data['uid']){
mlib_delete_file(MLIBPATH.'full/'.$data['url']);
mlib_delete_file(MLIBPATH.'thumb/'.$data['thumb']);
mysqli_query($mlib_db, "DELETE FROM `".MLIBPREFIX."uploads` WHERE `id`='".$val."' AND `uid`='".$mlib_current_user."'");
++$i;
}
}

echo $i.' Files were deleted from the seleted '.count($_POST['mlibid']).' files';
}


if($method=='mlib_create_import_method'){
$title = htmlentities($_POST['name'], ENT_QUOTES, "UTF-8");
$data = htmlentities($_POST['data'], ENT_QUOTES, "UTF-8");
mysqli_query($mlib_db, "INSERT INTO `".MLIBPREFIX."import` (`id`, `title`, `content`, `time`) VALUES (NULL, '$title', '$data', '".time()."')");
echo'Import method created successfully.';
}

if($method=='mlib_get_import_methods'){
$data = array();
$i = 0;
$qry = mysqli_query($mlib_db, "SELECT * FROM `".MLIBPREFIX."import`");
while($row = mysqli_fetch_assoc($qry)){
$data[$i] = $row;
$data[$i]['title'] = html_entity_decode($row['title'], ENT_QUOTES, "UTF-8");
$data[$i]['content'] = html_entity_decode($row['content'], ENT_QUOTES, "UTF-8");
$data[$i]['contentx'] = $row['content'];
++$i;
}

$data['total'] = $i;
echo json_encode($data);
}


if($method=='mlib_single_edit'){
$title = htmlentities($_POST['title'], ENT_QUOTES, "UTF-8");
$access = htmlentities($_POST['access'], ENT_QUOTES, "UTF-8");
$caption = htmlentities($_POST['caption'], ENT_QUOTES, "UTF-8");
$tagsx = htmlentities($_POST['tags'], ENT_QUOTES, "UTF-8");
$tags = format_tags($tagsx);

mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."uploads` SET `title`='$title', `caption`='$caption', `tags`='$tags', `access`='$access' WHERE `id`='".$_POST['mlibid']."'");


$zmails = $_POST['maillist']; 
mysqli_query($con, "DELETE FROM `".$prefix."access` WHERE `type`='photo' AND `aid`='".$_POST['mlibid']."'");

if(count($_POST['maillist'])>0){
foreach($zmails as $key => $val){
mysqli_query($con, "INSERT INTO `".$prefix."access` (`id`, `uid`, `aid`, `type`) VALUES (NULL, '$val', '".$_POST['mlibid']."', 'photo')");
}
}

$data['mlibid']=$_POST['mlibid'];
$data['title']=$title;
$data['access']=$access;
$data['caption']=$caption;
$data['tags']=$tags;
$data['emails']=str_replace(',', ', ', $emails);
$json = json_encode($data);
echo $json;
}



if($method=='mlib_photo_access'){

$xdata = mysqli_query($con, "SELECT `uid` FROM `".$prefix."access` WHERE `type`='photo' AND `aid`='".$_POST['aid']."'");

$uid = '';
$mails = array();
while($row = mysqli_fetch_assoc($xdata)){
$mails[] = $row['uid'];
}

$mdata = mysqli_query($con, "SELECT `email` FROM `".$prefix."users`");

$out = '';
while($xrow = mysqli_fetch_assoc($mdata)){
if(in_array($xrow['email'], $mails)){
$out = $out.'<option selected="selected" value="'.$xrow['email'].'">'.$xrow['email'].'</option>';
} else {
$out = $out.'<option value="'.$xrow['email'].'">'.$xrow['email'].'</option>';
}
}


echo $out;

}



if($method=='mlib_save_type'){
$title = htmlentities($_POST['title'], ENT_QUOTES, "UTF-8");
$content = htmlentities($_POST['content'], ENT_QUOTES, "UTF-8");
mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."import` SET `title`='$title', `content`='$content' WHERE `id`='".$_POST['mlibtypeid']."'");
}

/* Destroy db connection if it exists */
if($mlib_db){mysqli_close($mlib_db);}

}
?>
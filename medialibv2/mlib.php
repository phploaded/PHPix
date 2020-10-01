<?php 

include('mlib-config.php');

$method = $_POST['func'];



if($method == ''){
die('No direct access. No access identifier found.');
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


if($method == 'load_thumbs'){

if($_REQUEST['ipp']==''){$ipp=30;} else {$ipp=$_REQUEST['ipp'];}
if($_REQUEST['page']==''){$page=0;} else {$page=$_REQUEST['page']-1;}
$limit = $page * $ipp;

$i=0;
$data = array();
$complete = mysqli_fetch_assoc(mysqli_query($mlib_db, "SELECT COUNT(*) as `gtotal` FROM `".MLIBPREFIX."uploads` WHERE `uid`='$mlib_current_user' AND `folder`='".$_GET['fid']."'"));
$qry = "SELECT * FROM `".MLIBPREFIX."uploads` WHERE `uid`='$mlib_current_user' AND `folder`='".$_GET['fid']."' ORDER BY `time` DESC limit $limit, $ipp";
$res = mysqli_query($mlib_db, $qry);

while($row = mysqli_fetch_assoc($res)){
$row['newtime'] = date("l, jS M Y, h:i:s a", $row['time']);

$data[] = $row;
++$i;
}

$data['total'] = $i;
$data['page'] = $page+1;
$data['ipp'] = $ipp;
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
$caption = htmlentities($_POST['caption'], ENT_QUOTES, "UTF-8");
$tagsx = htmlentities($_POST['tags'], ENT_QUOTES, "UTF-8");
$tags = format_tags($tagsx);
mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."uploads` SET `title`='$title', `caption`='$caption', `tags`='$tags' WHERE `id`='".$_POST['mlibid']."'");

$data['mlibid']=$_POST['mlibid'];
$data['title']=$title;
$data['caption']=$caption;
$data['tags']=$tags;
$json = json_encode($data);
echo $json;
}

if($method=='mlib_save_type'){
$title = htmlentities($_POST['title'], ENT_QUOTES, "UTF-8");
$content = htmlentities($_POST['content'], ENT_QUOTES, "UTF-8");
mysqli_query($mlib_db, "UPDATE `".MLIBPREFIX."import` SET `title`='$title', `content`='$content' WHERE `id`='".$_POST['mlibtypeid']."'");
}

/* Destroy db connection if it exists */
if($mlib_db){mysqli_close($mlib_db);}
?>
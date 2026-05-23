<?php 

include('phpix-config.php');
if($_SESSION['PHPix']!=''){ $phpix_user = 1; } 
elseif($_SESSION['phpixuser']!=''){ $phpix_user = $_SESSION['phpixuser']; } 
else{$phpix_user ='';}
include('phpix-front-functions.php');


if($_GET['method']=='update_download'){
set_time_limit(3600);
include('phpix-info.php');
//$data = file_get_contents($software_zipURL.'updates/'.$_GET['v'].'.zip');
//$file = fopen(dirname(__FILE__) . '/downloads/a.apk', 'w+');

$file = $_GET['v'].'.zip';

@unlink('temp/'.$file);

$ch = curl_init($software_zipURL.''.$file);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$save = file_put_contents(
'temp/'.$file,
$response
);

$size = filesize('temp/'.$file);
$sizeKB = round(($size/1024), 2);

if($sizeKB<0.5){
echo'<p><b>Error for : </b>'.$software_zipURL.''.$file.'</p><b class="text-danger">Download Failed. Invalid response received. Total update size = '.$sizeKB.' Kb</b><br /><br />
<a href="phpix-manage.php?page=update" class="btn btn-medium btn-danger">Finish Update</a>';
@unlink('temp/'.$file);
} else {
echo'<b class="text-success">Download Completed. Total update size = '.$sizeKB.' Kb</b>
<script>verify_update(\''.$_GET['v'].'\');</script>';
}

}


if($_GET['method']=='update_verify'){

$file = $_GET['v'].'.zip';

rrmdir('temp/ext/');

$zip = new ZipArchive;
if ($zip->open('temp/'.$file) === TRUE) {
    $zip->extractTo('temp/ext/');
    $zip->close();

if(file_exists('temp/ext/patch.php')){
echo'<b class="text-success">Verified successfully. Ready to install the update!</b>
<script>install_update(\''.$_GET['v'].'\');</script>';
} else {
echo '<b class="text-danger">This is not a valid update file.</b><br /><br />
<button class="btn btn-medium btn-danger">Finish Update</button>';
}

} else {
echo '<b class="text-danger">Downloaded file was corrupted and failed to unzip.</b><br /><br />
<button class="btn btn-medium btn-danger">Finish Update</button>';
}

}


if($_GET['method']=='update_install'){

// running patch file
if(file_exists('temp/ext/patch.php')){
$x = file_get_contents($gallery_domain.'temp/ext/patch.php');
}

$file = $_GET['v'].'.zip';

// extract zip file
$zip = new ZipArchive;
if ($zip->open('temp/'.$file) === TRUE) {
    $zip->extractTo('./');
    $zip->close();
}

// delete zip file and patch file
@unlink('patch.php');
@unlink('temp/'.$file);

echo $x.'<b class="text-success">Installation completed. Redirecting in 3 seconds...</b>
<script>setTimeout("completed_update()", 3000);</script>';
}



if($_GET['method']=='read'){
$res = mysqli_query($con, "SELECT `spots` FROM `".$prefix."uploads` WHERE `id`='".$_GET['id']."'");
$data = mysqli_fetch_assoc($res);
echo $data['spots'];
}

// login in album
if($_GET['method']=='login'){

if(strlen($_POST['passkey'])>3){ 

$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']); 
$responseData = json_decode($verifyResponse); 

if($responseData->success){

$pwd = htmlentities($_POST['passkey'], ENT_QUOTES, "utf-8"); 
$email = htmlentities($_POST['email'], ENT_QUOTES, "utf-8"); 

$res = mysqli_query($con, "SELECT `id`,`email` FROM `".$prefix."users` WHERE `email`='$email' AND `pwd`='$pwd'");
$data = mysqli_fetch_assoc($res);
if($data['id']!=''){
$_SESSION['phpixuser'] = $data['email'];
echo'<div class="phpl-notify-success">Logged in successfully!</div><script>document.location.reload();</script>';
} else {
echo'<div class="phpl-notify-error">Wrong email or password. Retry!</div>';
}

} else {
echo'<div class="phpl-notify-error">reCaptcha verification failed. Retry!</div>';
}
}
}

if($_GET['method']=='logout'){
unset($_SESSION['phpixuser']);
}


if($_GET['method']=='save'){
	if ($_SESSION['PHPix'] != '' && $_POST['pic'] != '') {
		$spot_id = uniqid();
		$new_data[$spot_id] = $_POST;
		$spotLabel = trim((string) ($_POST['txt'] ?? ''));
		$spotTagId = 0;

			$select = $con->prepare("SELECT `id` FROM `".$prefix."spots` WHERE `title` = ? LIMIT 1");
			if ($select) {
				$select->bind_param('s', $spotLabel);
				$select->execute();
				$result = $select->get_result();
				$row = $result ? $result->fetch_assoc() : null;
				if ($result) {
					$result->free();
				}
				$select->close();
			}

			if (!empty($row['id'])) {
				$spotTagId = (int) $row['id'];
				$update = $con->prepare("UPDATE `".$prefix."spots` SET `sort` = `sort` + 1 WHERE `id` = ? LIMIT 1");
				if ($update) {
					$update->bind_param('i', $spotTagId);
					$update->execute();
					$update->close();
				}
			} else {
				$insert = $con->prepare("INSERT INTO `".$prefix."spots` (`uid`, `sort`, `title`) VALUES (?, 0, ?)");
				if ($insert) {
					$insert->bind_param('ss', $phpix_user, $spotLabel);
					$insert->execute();
					$spotTagId = (int) $insert->insert_id;
					$insert->close();
				}
			}
		
		$new_data[$spot_id]['txt'] = $spotLabel;
		if ($spotTagId > 0) {
			$new_data[$spot_id]['tid'] = $spotTagId;
		}
		unset($new_data[$spot_id]['sel']);
		$data = json_encode($new_data);
		$sql = "UPDATE `".$prefix."uploads` SET `spots`=concat(spots,'$data,') WHERE `id`='".$_POST['pic']."'";
		mysqli_query($con, $sql);
		echo $data;
	}
}



if($_GET['method']=='delete'){
$res = mysqli_query($con, "SELECT `spots` FROM `".$prefix."uploads` WHERE `id`='".$_GET['id']."'");
$data = mysqli_fetch_assoc($res);

$arr = json_decode('['.rtrim($data['spots'], ',').']', true);

for($i=0;$i<count($arr);$i++){
$arr2 = $arr[$i];

foreach($arr2 as $key => $value){
if($_GET['tid']==$key){
unset($arr[$i]);
}
}

}

if(count(array_values($arr))>0){
$newdata = str_replace('[', '', json_encode(array_values($arr)));
$newdata = str_replace(']', ',', $newdata);
} else {
$newdata = '';
}

mysqli_query($con, "UPDATE `".$prefix."uploads` SET `spots`='$newdata' WHERE `id`='".$_GET['id']."'");
}



if($_GET['method']=='delete_template_tag'){
$title = htmlentities($_GET['title'], ENT_QUOTES, "UTF-8");
mysqli_query($con, "DELETE FROM ".$prefix."spots WHERE `title` = '$title' AND `uid`='$phpix_user'");
}



if($_GET['method']=='album_notes'){

if($_GET['pagenumber']==''){
$page = 0;
} else {
$page = $_GET['pagenumber']-1;
}
$ipp = $_GET['ipp'];
$start = $page*$ipp;

$qry = "SELECT * FROM `".$prefix."content` WHERE `type`='note' AND `status`!='Disabled' ORDER BY `time` DESC limit ".$start.", ".$ipp." ";
$res = mysqli_query($con, $qry);

while($row = mysqli_fetch_assoc($res)){

echo'<div class="album-note-ctr">
<div class="album-note-box album-note-boxed">
<div class="album-title-pack">
<div class="album-note-title">'.$row['title'].'</div>
<div class="album-note-date">Updated on '.date("d-m-Y, h:i a", $row['time']).'</div>
</div>
<div class="album-note-descr">'.html_entity_decode($row['content'], ENT_QUOTES, "UTF-8").'</div>
'.$more.'
</div>
<div onclick="album_note_expand(this)" class="album-note-more">Read More</div>
</div>';
}



}



if($_GET['method']=='album_photos'){

$quality = $_GET['q'];

$album = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `".$prefix."albums` WHERE `id`='".$_GET['aid']."'"));

$oldthumb = '';
$newthumb = '';
$json = array();
$i = 0;


echo'<div class="album-bar-ctr">
<div class="album-bar">
<a href="javascript:void(0)" onclick="album_toggle_sidebar()" class="albtn-menu"></a>
<span>'.$album['title'].'</span>
<ul class="album-buttons">
<li class="albtn-deepshow gal-play"></li>
<li onclick="gal_share_album(\''.$album['id'].'\')" class="gal-share-social"></li>
<li onclick="toggleFullscreen(\'#flscrn\')" class="albtn-fullscreen"></li>
<li onclick="album_info_toggle()" class="albtn-albinfo"></li>
<li onclick="album_closePhotos()" class="albtn-back"></li>
</ul>
</div>
</div>

<div id="album-info">
<h2>'.$album['title'].'</h2>
<div class="album-descr">'.$album['descr'].'</div>
<i>Contains '.$album['count'].' Photos</i>,
<i class="album-created">Created on '.date($date_format, $album['created']).'</i>,
<i class="album-updated">Last updated on '.date($date_format, $album['updated']).'</i>
</div>';

gal_display_albums($_GET['aid']);

echo'<div class="gal-ctr">
<div class="notify"></div>';

if($_SESSION['PHPix']!=''){
$sql = "SELECT * FROM `".$prefix."uploads` WHERE (`access`='public' OR `access`='private') AND `folder`='".$_GET['aid']."'";
} elseif($_SESSION['phpixuser']==''){
$sql = "SELECT * FROM `".$prefix."uploads` WHERE `access`='public' AND `folder`='".$_GET['aid']."'";
} else {
$tql = mysqli_query($con, "SELECT * FROM `".$prefix."access` WHERE `type`='photo' AND `uid`='".$_SESSION['phpixuser']."'");
$nsql = '';
while($row = mysqli_fetch_assoc($tql)){
$nsql = $nsql." OR `id`='".$row['aid']."'";
}


$sql = "SELECT * FROM `".$prefix."uploads` WHERE".$esql." (`access`='public'".$nsql.") AND `folder`='".$_GET['aid']."'";
}



$data = mysqli_query($con, $sql);

while($row = mysqli_fetch_assoc($data))
{    
$thumb_file = get_thumb($row['url'], $quality);
$thumb = 'thumb/'.$thumb_file;
$quality_file = phpix_media_disk_path($quality, $row['url']);

/* checking both thumb and image ensures both are generated if not present via ajax */
if(file_exists($quality_file) && file_exists($thumb)){
list($thumb_width, $thumb_height) = getimagesize($thumb);

list($full_width, $full_height) = getimagesize('full/'.$row['url']);


$oldthumb = $oldthumb.'<li class="item" data-file="'.$row['url'].'" data-fw="'.$full_width.'" data-fh="'.$full_height.'" data-w="'.$thumb_width.'" data-h="'.$thumb_height.'"><a href="'.phpix_media_url($quality, $row['url']).'"><img xsrc="'.$gallery_domain.''.$thumb.'" src="'.$gallery_domain.''.$thumb.'"></a></li>';
$json['data'][$i]['w'] = $thumb_width;
$json['data'][$i]['u'] = $row['url'];
$json['data'][$i]['a'] = $row['access'];
$json['data'][$i]['fw'] = $full_width;
$json['data'][$i]['fh'] = $full_height;
++$i;
} else {
$newthumb = $newthumb.'<li data-access="'.$row['access'].'" data-url="'.$row['url'].'">'.$row['url'].'</li>';
}
}
$json['t']=$i;
$json['h']=$default_gallery_settings['thumb_height'];

echo'<div data-id="gallery" class="gal_data">'.json_encode($json).'</div>

<ul id="new_thumbs">'.$newthumb.'</ul>
</div><script>gal_vars_parentFolder = \''.$album['parent'].'\';</script>';

}



if($_GET['method']=='get_password'){

if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']); 
$responseData = json_decode($verifyResponse); 

if($responseData->success){

$cdata = mysqli_fetch_assoc(mysqli_query($con, "SELECT `pwd` FROM `".$prefix."users` WHERE `email`='".$_POST['email']."'"));

if($cdata['pwd']!=''){
	$msg ='This request was generated from PHPix gallery page from '.$_POST['xurl'].' . After logging in you can change the password yourself. Your current password is : '.$cdata['pwd'];
	if($_SERVER['HTTP_HOST']=='localhost'){
	echo '<b>Mail to : </b> '.$_POST['email'].' '.$msg;
	} else {
	mail($_POST['email'], "PHPix password", $msg);
	}
echo'<div class="phpl-notify-success">Email sent! Please check your inbox and spam folder!</div>';
} else {
	echo'<div class="phpl-notify-error">The email provided does not exist in our system!</div>';
}



} else {
echo'<div class="phpl-notify-error">reCaptcha verification failed. Retry!</div>';
}
} else {
echo'<div class="phpl-notify-warning">Your email seems invalid! Correct and retry!</div>';
}

}





if($_GET['method']=='change_password'){

$xpwd = $_POST['passkey'];
$npwd = $_POST['newpasskey'];
$cpwd = $_POST['cpasskey'];

$cdata = mysqli_fetch_assoc(mysqli_query($con, "SELECT `pwd` FROM `".$prefix."users` WHERE `email`='".$_SESSION['phpixuser']."'"));

if(strlen($xpwd)<8){
echo'<div class="phpl-notify-warning"><b>Current password</b> is too short! Minimum 8 charectors please!</div>';
}

elseif(strlen($npwd)<8){
echo'<div class="phpl-notify-warning"><b>New password is</b> too short! Minimum 8 charectors please!</div>';
}

elseif($cpwd!=$npwd){
echo'<div class="phpl-notify-error"><b>New password</b> and <b>Confirm password</b> do not match. Please retry!</div>';
}

elseif($xpwd!=$cdata['pwd']){
echo'<div class="phpl-notify-error"><b>Current password</b> is wrong! Correct it and retry!</div>';
}

else {

$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']); 
$responseData = json_decode($verifyResponse); 

if($responseData->success){

mysqli_query($con, "UPDATE `".$prefix."users` SET `pwd`='$npwd' WHERE `email`='".$_SESSION['phpixuser']."'");

echo'<div class="phpl-notify-success">Password was changed successfully!</div>';
} else {
echo'<div class="phpl-notify-error">reCaptcha verification failed!</div>';
}

}

}


if($_GET['method']=='album_get_spot'){

$quality = $_GET['q'];
$oldthumb = '';
$newthumb = '';
$json = array();
$i = 0;

echo'<div class="gal-ctr">
<div class="notify"></div>';

if ($_SESSION['PHPix'] != '') {
    // Query for logged-in PHPix session
    $sql = "SELECT * FROM `{$prefix}uploads` WHERE (`access`='public' OR `access`='private') AND `spots` LIKE ?";
    $stmt = $con->prepare($sql);
    $likeKey = '%' . $_GET['key'] . '%';
    $stmt->bind_param('s', $likeKey);
} elseif ($_SESSION['phpixuser'] == '') {
    // Query for guest users
    $sql = "SELECT * FROM `{$prefix}uploads` WHERE `access`='public' AND `spots` LIKE ?";
    $stmt = $con->prepare($sql);
    $likeKey = '%' . $_GET['key'] . '%';
    $stmt->bind_param('s', $likeKey);
} else {
    // Query for specific user access
    $tql = $con->prepare("SELECT `aid` FROM `{$prefix}access` WHERE `type`='photo' AND `uid`=?");
    $tql->bind_param('s', $_SESSION['phpixuser']);
    $tql->execute();
    $result = $tql->get_result();

    $nsqlParts = [];
    while ($row = $result->fetch_assoc()) {
        $nsqlParts[] = "`id` = " . intval($row['aid']); // Safely cast `aid` to integer
    }
    $nsql = implode(' OR ', $nsqlParts);

    // Construct final query
    $sql = "SELECT * FROM `{$prefix}uploads` WHERE (`access`='public' OR ($nsql)) AND `spots` LIKE ?";
    $stmt = $con->prepare($sql);
    $likeKey = '%' . $_GET['key'] . '%';
    $stmt->bind_param('s', $likeKey);
}

// Execute the prepared statement
$stmt->execute();
$data = $stmt->get_result();

while($row = $data->fetch_assoc())
{    
$thumb_file = get_thumb($row['url'], $quality);
$thumb = 'thumb/'.$thumb_file;
$quality_file = phpix_media_disk_path($quality, $row['url']);

/* checking both thumb and image ensures both are generated if not present via ajax */
if(file_exists($quality_file) && file_exists($thumb)){
list($thumb_width, $thumb_height) = getimagesize($thumb);
list($full_width, $full_height) = getimagesize('full/'.$row['url']);
$oldthumb = $oldthumb.'<li class="item" data-file="'.$row['url'].'" data-fw="'.$full_width.'" data-fh="'.$full_height.'" data-w="'.$thumb_width.'" data-h="'.$thumb_height.'"><a href="'.phpix_media_url($quality, $row['url']).'"><img xsrc="'.$gallery_domain.''.$thumb.'" src="'.$gallery_domain.''.$thumb.'"></a></li>';
$json['data'][$i]['w'] = $thumb_width;
$json['data'][$i]['u'] = $row['url'];
$json['data'][$i]['a'] = $row['access'];
$json['data'][$i]['fw'] = $full_width;
$json['data'][$i]['fh'] = $full_height;
++$i;
} else {
$newthumb = $newthumb.'<li data-access="'.$row['access'].'" data-url="'.$row['url'].'">'.$row['url'].'</li>';
}
}
$json['t']=$i;
$json['h']=$default_gallery_settings['thumb_height'];

echo'<div data-id="gallery" class="gal_data">'.json_encode($json).'</div>

<ul id="new_thumbs">'.$newthumb.'</ul>
</div><script>album_search_text(\''.$i.'\');</script>';

}



if($_GET['method']=='album_get_photos'){ 

$key = isset($_GET['key']) ? trim($_GET['key']) : '';
$key = '%' . $key . '%'; // Add wildcards for LIKE condition

$quality = isset($_GET['q']) ? htmlspecialchars(trim($_GET['q']), ENT_QUOTES, 'UTF-8') : 'hd';
$oldthumb = '';
$newthumb = '';
$json = array();
$i = 0;

echo '<div class="gal-ctr">
<div class="notify"></div>';

if ($_SESSION['PHPix'] != '') {
    $stmt = $con->prepare("SELECT * FROM `{$prefix}uploads` WHERE (`access`='public' OR `access`='private') AND (`title` LIKE ? OR `caption` LIKE ? OR `url` LIKE ?)");
    $stmt->bind_param('sss', $key, $key, $key);
} elseif ($_SESSION['phpixuser'] == '') {
    $stmt = $con->prepare("SELECT * FROM `{$prefix}uploads` WHERE `access`='public' AND (`title` LIKE ? OR `caption` LIKE ? OR `url` LIKE ?)");
    $stmt->bind_param('sss', $key, $key, $key);
} else {
    $uid = $_SESSION['phpixuser'];
    $stmt = $con->prepare("SELECT * FROM `{$prefix}uploads` WHERE `access`='public' AND `uid`=? AND (`title` LIKE ? OR `caption` LIKE ? OR `url` LIKE ?)");
    $stmt->bind_param('ssss', $uid, $key, $key, $key);
}

// Execute the statement and fetch results
if ($stmt->execute()) {
    $data = $stmt->get_result();

    while ($row = $data->fetch_assoc()) {
        $thumb_file = get_thumb($row['url'], $quality);
        $thumb = 'thumb/' . htmlspecialchars($thumb_file, ENT_QUOTES, 'UTF-8');
        $quality_file = phpix_media_disk_path($quality, $row['url']);

        /* Check both thumb and image ensure both are generated if not present via ajax */
        if (file_exists($quality_file) && file_exists('thumb/' . $thumb_file)) {
            list($thumb_width, $thumb_height) = getimagesize($thumb);
            list($full_width, $full_height) = getimagesize('full/' . $row['url']);

            $oldthumb .= '<li class="item" data-file="' . htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8') . '" data-fw="' . htmlspecialchars($full_width, ENT_QUOTES, 'UTF-8') . '" data-fh="' . htmlspecialchars($full_height, ENT_QUOTES, 'UTF-8') . '" data-w="' . htmlspecialchars($thumb_width, ENT_QUOTES, 'UTF-8') . '" data-h="' . htmlspecialchars($thumb_height, ENT_QUOTES, 'UTF-8') . '">
                <a href="' . htmlspecialchars(phpix_media_url($quality, $row['url']), ENT_QUOTES, 'UTF-8') . '">
                    <img xsrc="' . htmlspecialchars($gallery_domain . $thumb, ENT_QUOTES, 'UTF-8') . '" src="' . htmlspecialchars($gallery_domain . $thumb, ENT_QUOTES, 'UTF-8') . '">
                </a>
            </li>';

            $json['data'][$i]['w'] = $thumb_width;
            $json['data'][$i]['u'] = htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8');
            $json['data'][$i]['a'] = htmlspecialchars($row['access'], ENT_QUOTES, 'UTF-8');
            $json['data'][$i]['fw'] = $full_width;
            $json['data'][$i]['fh'] = $full_height;
            ++$i;
        } else {
            $newthumb .= '<li data-access="' . htmlspecialchars($row['access'], ENT_QUOTES, 'UTF-8') . '" data-url="' . htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8') . '</li>';
        }
    }
} else {
    echo "<script>phpl_alert('Error executing query.')</script>";
}

// Add metadata to the JSON response
$json['t'] = $i;
$json['h'] = htmlspecialchars($default_gallery_settings['thumb_height'], ENT_QUOTES, 'UTF-8');

echo '<div data-id="gallery" class="gal_data">' . json_encode($json, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . '</div>

<ul id="new_thumbs">' . $newthumb . '</ul>
</div><script>album_search_text(\'' . htmlspecialchars($i, ENT_QUOTES, 'UTF-8') . '\');</script>';

}


if($_GET['method']=='album_get_folders'){

echo'';

$key = isset($_GET['key']) ? trim(mysqli_real_escape_string($con, $_GET['key'])) : '';
gal_display_albums('', $key);

echo'<div class="gal-ctr">
<div class="notify"></div>
</div>';
}




// no ending php tag, intentionally

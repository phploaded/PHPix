<?php 

include('phpix-config.php');
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
echo'<b class="text-danger">Download Failed. Invalid response received.</b><br /><br />
<button class="btn btn-medium btn-danger">Finish Update</button>';
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
	if($_SESSION['PHPix']!='' && $_POST['pic']!=''){
	$spot_id = uniqid();
	$new_data[$spot_id] = $_POST;
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
<a href="#" onclick="album_toggle_sidebar()" class="albtn-menu"></a>
<span>'.$album['title'].'</span>
<ul class="album-buttons">
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
</div>

<div class="gal-ctr">
<div class="notify"></div>';

$data = mysqli_query($con, "SELECT * FROM `".$prefix."uploads` WHERE `folder`='".$_GET['aid']."'");

while($row = mysqli_fetch_assoc($data))
{    
$thumb = 'thumb/'.$row['thumb'];

/* checking both thumb and image ensures both are generated if not present via ajax */
if(file_exists($quality.'/'.$row['thumb']) && file_exists('thumb/'.$row['thumb'])){
list($thumb_width, $thumb_height) = getimagesize($thumb);
$oldthumb = $oldthumb.'<li class="item" data-w="'.$thumb_width.'" data-h="'.$thumb_height.'"><a href="'.$gallery_domain.''.$quality.'/'.$row['url'].'"><img src="'.$gallery_domain.''.$thumb.'"></a></li>';
$json['data'][$i]['w'] = $thumb_width;
$json['data'][$i]['u'] = $row['thumb'];
++$i;
} else {
$newthumb = $newthumb.'<li data-url="'.$row['url'].'">'.$row['url'].'</li>';
}
}
$json['t']=$i;
$json['h']=$default_gallery_settings['thumb_height'];

echo'<div data-id="gallery" class="gal_data">'.json_encode($json).'</div>

<ul id="new_thumbs">'.$newthumb.'</ul>
</div>';

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





// no ending php tag, intentionally
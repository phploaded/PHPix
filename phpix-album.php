<?php 

include('phpix-config.php');
include('phpix-front-functions.php');

?><!DOCTYPE html>
<html>
<head>
<?php include('phpix-scripts.php'); ?>
<title>PHPix Photo Gallery</title>
</head>
<body>
<div id="flscrn">
<div id="album-sidebar">
<ul id="album-sidebar-menu">
<li onclick="album_gallery_main()">PHPix Gallery albums</li>
<li onclick="album_nav_keys()">Keyboard Navigation Buttons</li>
<li onclick="album_notes();album_toggle_sidebar();">Public news and notebook</li>
<li onclick="gal_settings()">User Interface Settings</li>
<?php if($_SESSION['phpixuser']==''){
echo'<li onclick="album_get_pwd()">Retrieve your password</li>
<li onclick="album_login()">Login</li>';
} else {
	
echo'<li onclick="album_change_pwd()">Change your password</li>
<li onclick="album_logout()">Logout</li>';
} ?>
</ul>
</div>
<div id="flscrn-inner">
<div id="album-list-block">
<div class="album-bar-ctr">
<div class="album-bar">
<a onclick="album_toggle_sidebar()" class="albtn-menu"></a>
<span>Public albums</span>
<ul class="album-buttons">
<li onclick="toggleFullscreen('#flscrn');" class="albtn-fullscreen"></li>
</ul>
</div>
</div>

<div class="album-ctr">

<div xontouchstart="gal_touchtip('Touch photo for info. Touch info to view album.')" xoncontextmenu="return false;" class="album-list ximghvr">
<?php 

$classes = array(
"imghvr-fade", "imghvr-push-up", "imghvr-push-down", "imghvr-push-left",
"imghvr-push-right", "imghvr-slide-up", "imghvr-slide-down", "imghvr-slide-left",
"imghvr-slide-right", "imghvr-reveal-up", "imghvr-reveal-down", "imghvr-reveal-left",
"imghvr-reveal-right", "imghvr-hinge-up", "imghvr-hinge-down", "imghvr-hinge-left", 
"imghvr-hinge-right", "imghvr-flip-horiz", "imghvr-flip-vert", "imghvr-flip-diag-1",
"imghvr-flip-diag-2", "imghvr-shutter-out-horiz", "imghvr-shutter-out-vert", 
"imghvr-shutter-out-diag-1", "imghvr-shutter-out-diag-2", "imghvr-shutter-in-horiz",
"imghvr-shutter-in-vert", "imghvr-shutter-in-out-horiz", "imghvr-shutter-in-out-vert",
"imghvr-shutter-in-out-diag-1", "imghvr-shutter-in-out-diag-2", "imghvr-fold-up",
"imghvr-fold-down", "imghvr-fold-left", "imghvr-fold-right", "imghvr-zoom-in",
"imghvr-zoom-out", "imghvr-zoom-out-up", "imghvr-zoom-out-down", "imghvr-zoom-out-left",
"imghvr-zoom-out-right", "imghvr-zoom-out-flip-horiz", "imghvr-zoom-out-flip-vert",
"imghvr-blur"
);

function randomColor(){
    $result = array('rgb' => '', 'hex' => '');
    foreach(array('r', 'b', 'g') as $col){
        $rand = mt_rand(0, 255);
        $result['rgb'][$col] = $rand;
        $dechex = dechex($rand);
        if(strlen($dechex) < 2){
            $dechex = '0' . $dechex;
        }
        $result['hex'] .= $dechex;
    }
    return $result;
}


if($_SESSION['phpixuser']==''){
$sql = "SELECT * FROM `".$prefix."albums` WHERE `access`='public'";
} else {

$tql = mysqli_query($con, "SELECT * FROM `".$prefix."access` WHERE `uid`='".$_SESSION['phpixuser']."'");
$nsql = '';
while($row = mysqli_fetch_assoc($tql)){
$nsql = $nsql." OR `id`='".$row['aid']."'";
}


$sql = "SELECT * FROM `".$prefix."albums` WHERE `access`='public'".$nsql;
}


$data = mysqli_query($con, $sql);

while($row = mysqli_fetch_assoc($data))
{ 

if($row['thumb']!=''){
$photo = $gallery_domain.'cover/'.$row['thumb'];
} else {
$photo = $gallery_domain.'phpix-libs/images/holder.svg';
}

$key = array_rand($classes,1);
unset($color);
$color = randomColor();
//print_r($color);
echo '<div class="album-box"><div class="album-ctr album-type-'.$row['access'].'"><figure style="background-color:#'.$color['hex'].'" xclass="imghvr-reveal-left" class="'.$classes[$key].'">
<img src="'.$photo.'">
<div class="album-preview"><div class="album-preview-title">'.$row['title'].'<span>'.$row['count'].' Photos</span></div></div>
<figcaption>
<div class="album-info">
<ul class="album-date">
<li>'.$row['count'].' photos - <b onclick="gal_gotoURL(this)" xurl="'.$gallery_domain.''.$albumFILE.'?aid='.$row['id'].'">VIEW ALBUM</b></li>
<li>Last updated on '.date($date_format, $row['updated']).'</li>
</ul>
<p>'.$row['descr'].'</p>
</div>
</figcaption>
</figure></div></div>';

$x='<div class="album-box"><div ontouchmove="gal_album_focus(this)" class="album-ctr"><div class="album-out">
<div class="album">
<img src="'.$photo.'">
<div class="album-preview">
<div class="album-preview-title">'.$row['title'].'<span>'.$row['count'].' Photos</span></div>
</div>

</div>

<div class="album-info">
<h2><a href="'.$gallery_domain.'album.php?aid='.$row['id'].'">'.$row['title'].'</a></h2>
<ul class="album-date">
<li>'.$row['count'].' photos</li>
<li>Created on '.date($date_format, $row['created']).'</li>
<li>Updated on '.date($date_format, $row['updated']).'</li>
</ul>
<p>'.$row['descr'].'</p>
</div>



</div></div></div>';
}

 ?>
</div>
</div>
</div>


<div id="album-pics-block"></div>



<div id="album-notes-block">
<div class="album-bar-ctr">
<div class="album-bar">
<a href="#" onclick="album_toggle_sidebar()" class="albtn-menu"></a>
<span>Notes</span>
<ul class="album-buttons">
<li onclick="toggleFullscreen('#flscrn')" class="albtn-fullscreen"></li>
<li onclick="album_closePhotos()" class="albtn-back"></li>
</ul>
</div>
</div>

<div class="album-notes-ctr">
<div class="album-pages"></div>
<?php 

$count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `".$prefix."content` WHERE `type`='note' AND `status`='Enabled'"));

if($_GET['pagenumber']==''){
$cpage = 1;
} else {
$cpage = $_GET['pagenumber'];
}

echo'<script>
$(function() {
    $(".album-pages").pagination({
        items: '.$count['total'].',
        itemsOnPage: gal_vars_notes_ipp,
        cssStyle: \'light-theme\',
		currentPage: '.$cpage.',
		hrefTextPrefix: "'.$albumFILE.'?tab=notes&ipp="+gal_vars_notes_ipp+"&pagenumber=",
		hrefTextSuffix: "",
		onPageClick: function(pgno, e){e.preventDefault();get_album_notes(pgno);}
    });
});
</script>';

 ?>
<div class="album-notes"></div>
</div>

</div>


</div></div>



</body>
</html>
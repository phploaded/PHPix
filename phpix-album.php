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
<li onclick="album_gallery_main()">Gallery - Show Albums</li>
<li onclick="album_nav_keys()">Keyboard Navigation Buttons</li>
<li onclick="album_notes();album_toggle_sidebar();">Public news and notebook</li>
<li onclick="gal_settings()">User Interface Settings</li>
<?php 
if($_SESSION['phpixuser']==''){
echo'<li><a target="_blank" href="'.$admin_url.'index">Administration Area</a></li>';
}

if($_SESSION['PHPix']==''){
if($_SESSION['phpixuser']==''){
echo'<li onclick="album_get_pwd()">Retrieve your password</li>
<li onclick="album_login()">User Login</li>';
} else {
	
echo'<li><a href="'.$admin_url.'albums">Manage your albums</a></li>
<li onclick="album_change_pwd()">Change your password</li>
<li onclick="album_logout()">User Logout</li>';
} 
}

?>
<li onclick="album_about()">About PHPix Gallery</li>
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


<?php 
gal_display_albums();
 ?>

</div>


<div id="album-pics-block"></div>



<div id="album-notes-block">
<div class="album-bar-ctr">
<div class="album-bar">
<a href="javascript:void(0)" onclick="album_toggle_sidebar()" class="albtn-menu"></a>
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